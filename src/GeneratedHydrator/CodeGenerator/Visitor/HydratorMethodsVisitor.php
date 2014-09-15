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
     * @var ReflectionClass
     */
    private $reflectedClass;

    /**
     * @var ReflectionProperty[]
     */
    private $accessibleProperties;

    /**
     * @var PropertyAccessor[]
     */
    private $propertyWriters = array();

    /**
     * @param ReflectionClass $reflectedClass
     */
    public function __construct(ReflectionClass $reflectedClass)
    {
        $this->reflectedClass       = $reflectedClass;
        $this->accessibleProperties = $this->getProtectedProperties($reflectedClass);

        foreach ($this->getPrivateProperties($reflectedClass) as $property) {
            $this->propertyWriters[$property->getName()] = new PropertyAccessor($property, 'Writer');
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
            $property         = $originalProperty->getName();

            $bodyParts[] = "\$this->" . $accessorName . " = \\Closure::bind(function (\$object, \$value) {\n"
                . "    \$object->" . $property . " = \$value;\n"
                . "}, null, " . var_export($className, true) . ");";
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

        foreach ($this->accessibleProperties as $accessibleProperty) {
            $body .= '$object->'
                . $accessibleProperty->getName()
                . ' = $data['
                . var_export($accessibleProperty->getName(), true)
                . "];\n";
        }

        foreach ($this->propertyWriters as $propertyWriter) {
            $body .= '$this->'
                . $propertyWriter->props[0]->name
                . '->__invoke($object, $data['
                . var_export($propertyWriter->getOriginalProperty()->getName(), true)
                . "]);\n";
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
            // no properties to hydrate

            $method->stmts = $parser->parse('<?php return array();');

            return;
        }

        $body = '';

        if (! empty($this->propertyWriters)) {
            $body = "\$data = (array) \$object;\n\n";
        }

        $body .= 'return array(';

        foreach ($this->accessibleProperties as $accessibleProperty) {
            if (empty($this->propertyWriters) || ! $accessibleProperty->isProtected()) {
                $body .= "\n    "
                    . var_export($accessibleProperty->getName(), true)
                    . ' => $object->' . $accessibleProperty->getName() . ',';
            } else {
                $body .= "\n    "
                    . var_export($accessibleProperty->getName(), true)
                    . ' => $data["\\0*\\0' . $accessibleProperty->getName() . '"],';
            }
        }

        foreach ($this->propertyWriters as $propertyWriter) {
            $property     = $propertyWriter->getOriginalProperty();
            $propertyName = $property->getName();

            $body .= "\n    "
                . var_export($propertyName, true)
                . ' => $data["'
                . '\\0' . $property->getDeclaringClass()->getName()
                . '\\0' . $propertyName
                . '"],';
        }

        $body .= "\n);";

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
    private function getProtectedProperties(ReflectionClass $reflectedClass)
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
