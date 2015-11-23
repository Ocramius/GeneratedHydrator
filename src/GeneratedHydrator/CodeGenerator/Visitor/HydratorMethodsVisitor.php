<?php

namespace GeneratedHydrator\CodeGenerator\Visitor;

use GeneratedHydrator\ClassGenerator\Hydrator\PropertyGenerator\PropertyAccessor;
use PhpParser\Lexer;
use PhpParser\Node;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\NodeVisitorAbstract;
use PhpParser\Parser;
use ReflectionClass;
use ReflectionProperty;

/**
 * Replaces methods `__construct`, `hydrate` and `extract` in the classes of the given AST
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
class HydratorMethodsVisitor extends NodeVisitorAbstract
{
    /**
     * When this option is passed, only the properties in the given array are
     * hydrated and extracted.
     */
    const OPTION_ALLOWED_PROPERTIES = 'allowedProperties';

    /**
     * @var array Holds configuration for the object properties.
     */
    private $allowedProperties;

    /**
     * @var ReflectionClass
     */
    private $reflectedClass;

    /**
     * @var ReflectionProperty[]
     */
    private $accessibleProperties;

    /**
     * This variable only holds private properties.
     *
     * @var PropertyAccessor[]
     */
    private $propertyWriters = array();

    /**
     * @param ReflectionClass $reflectedClass
     */
    public function __construct(ReflectionClass $reflectedClass, array $options = [])
    {
        $this->reflectedClass       = $reflectedClass;
        $this->accessibleProperties = $this->getAccessibleProperties($reflectedClass);
        $this->allowedProperties    = $this->expandAllowedProperties($options);

        foreach ($this->getPrivateProperties($reflectedClass) as $property) {
            $this->propertyWriters[$property->getName()] = new PropertyAccessor($property, 'Writer');
        }
    }

    /**
     * Returns an array with properties as keys and hydrate/extract information
     * as values.
     *
     * @param type $allowedProperties
     */
    private function expandAllowedProperties($options)
    {
        $allowedProperties = [];
        $propertyNames = array_map(function($prop) {
            return $prop->name;
        }, $this->reflectedClass->getProperties());

        if (! isset($options[static::OPTION_ALLOWED_PROPERTIES])) {
            foreach ($propertyNames as $propertyName) {
                $allowedProperties[$propertyName] = [
                    'extract' => true,
                    'hydrate' => true
                ];
            }

            return $allowedProperties;
        }

        if (! is_array($options[static::OPTION_ALLOWED_PROPERTIES])) {
            throw new \InvalidArgumentException(sprintf('OPTION_ALLOWED_PROPERTIES is given but it\'s value is of type %s which should be an array.', gettype($options[static::OPTION_ALLOWED_PROPERTIES])));
        }

        foreach ($options[static::OPTION_ALLOWED_PROPERTIES] as $k => $v) {
            // simple format
            if (is_int($k)) {
                if (! is_string($v)) {
                    throw new \InvalidArgumentException(sprintf('Invalid value of type %s found on index %s, expected a string.', gettype($v), $k));
                }

                if (in_array($v, array_keys($allowedProperties))) {
                    throw new \InvalidArgumentException(sprintf('Property "%s" was supplied in simple and advanced format, only one is allowed.', $v));
                }

                $allowedProperties[$v] = [
                    'extract' => true,
                    'hydrate' => true
                ];

                continue;
            }

            // advanced format
            if (is_string($k)) {
                if (! is_array($v)) {
                    throw new \InvalidArgumentException(sprintf('Property "%s" was supplied as key, but the value is of type %s and an array was expected.', $k, gettype($v)));
                }

                if (in_array($k, $allowedProperties)) {
                    throw new \InvalidArgumentException(sprintf('Property "%s" was supplied in simple and advanced format, only one is allowed.', $v));
                }

                $validateOptionConfigurationKey = function($property, $array, $key) {
                    if (! isset($array[$key])) {
                        throw new \InvalidArgumentException(sprintf('Property "%s" is missing key "%s".', $property, $key));
                    }

                    if (! in_array($array[$key], [true, false, 'optional'])) {
                        throw new \InvalidArgumentException(sprintf('Property "%s" has an invalid value for key "$s".', $property, $key));
                    }
                };

                $validateOptionConfigurationKey($k, $v, 'extract');
                $validateOptionConfigurationKey($k, $v, 'hydrate');

                $allowedProperties[$k] = $v;
            }
        }

        // Disable all properties which are not specified in the allowedProperties
        foreach ($propertyNames as $propertyName) {
            if (! in_array($propertyName, array_keys($allowedProperties))) {
                $allowedProperties[$propertyName] = [
                    'extract' => false,
                    'hydrate' => false
                ];
            }
        }

        return $allowedProperties;
    }

    /**
     * @param Node $node
     *
     * @return null|Class_|void
     */
    public function leaveNode(Node $node)
    {
        if (! $node instanceof Class_) {
            return null;
        }

        $this->replaceConstructor($this->findOrCreateMethod($node, '__construct'));
        $this->replaceHydrate($this->findOrCreateMethod($node, 'hydrate'));
        $this->replaceExtract($this->findOrCreateMethod($node, 'extract'));

        return $node;
    }

    /**
     * @param ClassMethod $method
     */
    private function replaceConstructor(ClassMethod $method)
    {
        $method->params = array();

        $bodyParts = array();

        foreach ($this->propertyWriters as $propertyWriter) {
            $accessorName     = $propertyWriter->props[0]->name;
            $originalProperty = $propertyWriter->getOriginalProperty();
            $className        = $originalProperty->getDeclaringClass()->getName();
            $propertyName     = $originalProperty->getName();

            if (in_array($this->allowedProperties[$propertyName]['hydrate'], [true, 'optional'])) {
                $bodyParts[] = "\$this->" . $accessorName . " = \\Closure::bind(function (\$object, \$value) {\n"
                    . "    \$object->" . $propertyName . " = \$value;\n"
                    . "}, null, " . var_export($className, true) . ");";
            }
        }

        $parser = new Parser(new Lexer());

        $method->stmts = $parser->parse('<?php ' . implode("\n", $bodyParts));
    }

    /**
     * @param ClassMethod $method
     */
    private function replaceHydrate(ClassMethod $method)
    {
        $method->params = array(
            new Param('data', null, 'array'),
            new Param('object'),
        );

        $body = '';

        $replaceWithOption = function($option, $assignment, $keyName) {
            if ($option === true) {
                return $assignment;
            } elseif ($option === 'optional') {
                return 'if (isset($data[' . $keyName . "])) {\n"
                    . $assignment
                    . "}\n";
            }
        };

        foreach ($this->accessibleProperties as $accessibleProperty) {
            $propertyName = $accessibleProperty->getName();
            $keyName = var_export($accessibleProperty->getName(), true);
            $option = $this->allowedProperties[$propertyName]['hydrate'];

            if ($option === false) {
                continue;
            }

            $assignment = '$object->'
                . $propertyName
                . ' = $data['
                . $keyName
                . "];\n";

            $body .= $replaceWithOption($option, $assignment, $keyName);
        }

        foreach ($this->propertyWriters as $propertyWriter) {
            $propertyWriterName = $propertyWriter->props[0]->name;
            $keyName = var_export($propertyWriter->getOriginalProperty()->getName(), true);
            $option = $this->allowedProperties[$propertyWriter->getOriginalProperty()->name]['hydrate'];

            if ($option === false) {
                continue;
            }

            $assignment = '$this->'
                . $propertyWriterName
                . '->__invoke($object, $data['
                . $keyName
                . "]);\n";

            $body .= $replaceWithOption($option, $assignment, $keyName);
        }

        $body .= "\nreturn \$object;";

        $parser = new Parser(new Lexer());

        $method->stmts = $parser->parse('<?php ' . $body);
    }

    /**
     * @param ClassMethod $method
     */
    private function replaceExtract(ClassMethod $method)
    {
        $parser = new Parser(new Lexer());

        $method->params = array(new Param('object'));

        if (empty($this->accessibleProperties) && empty($this->propertyWriters)) {
            // the object does not have any properties

            $method->stmts = $parser->parse('<?php return array();');

            return;
        }

        $body = '';

        // This flag is being used to determine if protected properties get their
        // data from an array or directly from the object itself
        $hasPrivatePropertiesWhichNeedExtract = false;
        foreach ($this->propertyWriters as $p) {
            if (in_array($this->allowedProperties[$p->getOriginalProperty()->name]['extract'], [true, 'optional'])) {
                $hasPrivatePropertiesWhichNeedExtract = true;
            }
        }

        if ($hasPrivatePropertiesWhichNeedExtract) {
            $body = "\$data = (array) \$object;\n\n";
        }

        // Make code for the properties which can be assigned right away in the array
        $assignments = [];

        foreach ($this->accessibleProperties as $accessibleProperty) {
            $propertyName = $accessibleProperty->getName();

            if (! $hasPrivatePropertiesWhichNeedExtract || ! $accessibleProperty->isProtected()) {
                $propertyData = '$object->' . $propertyName;
            } else {
                $propertyData = '$data["\\0*\\0' . $propertyName . '"]';
            }

            $assignments[$propertyName] = "\n    "
                . var_export($propertyName, true)
                . ' => ' . $propertyData . ',';
        }

        foreach ($this->propertyWriters as $propertyWriter) {
            $property     = $propertyWriter->getOriginalProperty();
            $propertyName = $property->getName();

            $assignments[$propertyName] = "\n    "
                . var_export($propertyName, true)
                . ' => $data["'
                . '\\0' . $property->getDeclaringClass()->getName()
                . '\\0' . $propertyName
                . '"],';
        }

        // None of the extract properties are optional
        if (count(array_filter($this->allowedProperties, function($conf) {
            return $conf['extract'] === 'optional';
        })) === 0) {
            $body .= 'return array(';
            foreach ($assignments as $propertyName => $a) {
                if ($this->allowedProperties[$propertyName]['extract'] === true) {
                    $body .= $a;
                }
            }
            $body .= "\n);";

            $method->stmts = $parser->parse('<?php ' . $body);

            return;
        }

        // Has extract properties which are optional
        $body .= '$ret = array(';
        foreach ($assignments as $propertyName => $a) {
            if ($this->allowedProperties[$propertyName]['extract'] === true) {
                $body .= $a;
            }
        }
        $body .= "\n);\n";

        foreach ($this->accessibleProperties as $accessibleProperty) {
            $propertyName = $accessibleProperty->getName();

            if ($this->allowedProperties[$propertyName]['extract'] === 'optional') {
                if (! $hasPrivatePropertiesWhichNeedExtract || ! $accessibleProperty->isProtected()) {
                    $propertyData = '$object->' . $propertyName;
                } else {
                    $propertyData = '$data["\\0*\\0' . $propertyName . '"]';
                }

                $body .= 'if (isset(' . $propertyData . ")) {\n"
                    . '    $ret[' . var_export($propertyName, true) . '] = ' . $propertyData . ";\n"
                    . "}\n";
            }
        }

        foreach ($this->propertyWriters as $propertyWriter) {
            $property     = $propertyWriter->getOriginalProperty();
            $propertyName = $property->getName();

            if ($this->allowedProperties[$propertyName]['extract'] === 'optional') {
                $propertyData = '$data["'
                    . '\\0' . $property->getDeclaringClass()->getName()
                    . '\\0' . $propertyName
                    . '"]';

                $body .= 'if (isset(' . $propertyData . ")) {\n"
                    . '    $ret[' . var_export($propertyName, true) . '] = ' . $propertyData . ";\n"
                    . "}\n";
            }
        }

        $body .= "\nreturn \$ret;";

        $method->stmts = $parser->parse('<?php ' . $body);
    }

    /**
     * Finds or creates a class method (and eventually attaches it to the class itself)
     *
     * @param Class_ $class
     * @param string                    $name  name of the method
     *
     * @return ClassMethod
     */
    private function findOrCreateMethod(Class_ $class, $name)
    {
        $foundMethods = array_filter(
            $class->getMethods(),
            function (ClassMethod $method) use ($name) {
                return $name === $method->name;
            }
        );

        $method = reset($foundMethods);

        if (!$method) {
            $class->stmts[] = $method = new ClassMethod($name);
        }

        return $method;
    }

    /**
     * Retrieve instance public/protected properties
     *
     * @param ReflectionClass $reflectedClass
     *
     * @return ReflectionProperty[]
     */
    private function getAccessibleProperties(ReflectionClass $reflectedClass)
    {
        return array_filter(
            $reflectedClass->getProperties(),
            function (ReflectionProperty $property) {
                return ($property->isPublic() || $property->isProtected()) && ! $property->isStatic();
            }
        );
    }

    /**
     * Retrieve instance private properties
     *
     * @param ReflectionClass $reflectedClass
     *
     * @return ReflectionProperty[]
     */
    private function getPrivateProperties(ReflectionClass $reflectedClass)
    {
        return array_filter(
            $reflectedClass->getProperties(),
            function (ReflectionProperty $property) {
                return $property->isPrivate() && ! $property->isStatic();
            }
        );
    }
}
