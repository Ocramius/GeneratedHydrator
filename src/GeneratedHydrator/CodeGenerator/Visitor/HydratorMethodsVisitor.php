<?php

namespace GeneratedHydrator\CodeGenerator\Visitor;

use GeneratedHydrator\ClassGenerator\AllowedPropertiesOption;
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
     * @var array Holds configuration for the object properties.
     */
    private $allowedProperties;

    /**
     * @var ReflectionProperty[]
     */
    private $accessibleProperties;

    /**
     * This variable only holds private properties.
     *
     * @var PropertyAccessor[]
     */
    private $propertyWriters = [];

    /**
     * This flag is being used to determine if protected properties get their
     * data from an array or directly from the object itself
     *
     * @var bool
     */
    private $hasPrivatePropertiesWhichNeedExtracting = false;

    /**
     * @param ReflectionClass $reflectedClass
     */
    public function __construct(array $accessibleProperties, array $propertyWriters, AllowedPropertiesOption $option)
    {
        $this->propertyWriters = $propertyWriters;
        $this->accessibleProperties = $accessibleProperties;
        $this->allowedProperties = $option->getAllowedProperties();

        foreach ($this->propertyWriters as $propertyWriter) {
            $allowedPropertyExtract = $this->allowedProperties[$propertyWriter->getOriginalProperty()->name]['extract'];

            if (in_array($allowedPropertyExtract, [true, 'optional'])) {
                $this->hasPrivatePropertiesWhichNeedExtracting = true;
                break;
            }
        }
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
            }

            if ($option === 'optional') {
                return 'if (isset($data[' . $keyName . ']) OR array_key_exists(' . $keyName . ", \$data)) {\n"
                    . $assignment
                    . "}\n";
            }
        };

        foreach ($this->accessibleProperties as $accessibleProperty) {
            $propertyName = $accessibleProperty->getName();
            $keyName = var_export($accessibleProperty->getName(), true);
            $option = $this->allowedProperties[$propertyName]['hydrate'];

            if (false === $option) {
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

            if (false === $option) {
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

        if ($this->hasPrivatePropertiesWhichNeedExtracting) {
            $body = "\$data = (array) \$object;\n\n";
        }

        // Make code for the properties which can be assigned right away in the array
        $assignments = [];

        foreach ($this->accessibleProperties as $accessibleProperty) {
            $propertyName = $accessibleProperty->getName();

            if (! $this->hasPrivatePropertiesWhichNeedExtracting || ! $accessibleProperty->isProtected()) {
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
        if (! array_filter($this->allowedProperties, function($conf) {
            return $conf['extract'] === 'optional';
        })) {
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
                if (! $this->hasPrivatePropertiesWhichNeedExtracting || ! $accessibleProperty->isProtected()) {
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
}
