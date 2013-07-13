<?php

namespace GeneratedHydrator\CodeGenerator\Visitor\Hydrator;

use GeneratedHydrator\ClassGenerator\Hydrator\PropertyGenerator\PropertyAccessor;
use PHPParser_Lexer;
use PHPParser_Lexer_Emulative;
use PHPParser_Node;
use PHPParser_Node_Stmt_Class;
use PHPParser_Node_Stmt_ClassMethod;
use PHPParser_NodeVisitorAbstract;
use PHPParser_Parser;
use ReflectionClass;

class HydratorMethodsVisitor extends PHPParser_NodeVisitorAbstract
{
    /**
     * @var ReflectionClass
     */
    private $reflectedClass;

    /**
     * @var PropertyAccessor[]
     */
    private $propertyWriters;

    /**
     * @param ReflectionClass $reflectedClass
     */
    public function __construct(ReflectionClass $reflectedClass)
    {
        $this->reflectedClass = $reflectedClass;

        // @todo over-simplified for testing
        foreach ($reflectedClass->getProperties(\ReflectionProperty::IS_PRIVATE) as $property) {
            $this->propertyWriters[$property->getName()] = new PropertyAccessor($property, 'Writer');
        }

    }

    public function enterNode(PHPParser_Node $node)
    {
        if (! $node instanceof PHPParser_Node_Stmt_Class) {
            return null;
        }

        $this->replaceConstructor($this->findOrCreateMethod($node, '__construct'));
        $this->replaceHydrate($this->findOrCreateMethod($node, 'hydrate'));
        $this->replaceExtract($this->findOrCreateMethod($node, 'extract'));

        return $node;
    }

    private function replaceConstructor(PHPParser_Node_Stmt_ClassMethod $method = null)
    {
        $method->params = array();

        $bodyParts = array();

        foreach ($this->propertyWriters as $propertyWriter) {
            $accessorName     = $propertyWriter->getName();
            $originalProperty = $propertyWriter->getOriginalProperty();
            $className        = $originalProperty->getDeclaringClass()->getName();
            $property         = $originalProperty->getName();

            $bodyParts[] = "\$this->" . $accessorName . " = \\Closure::bind(function (\$object, \$value) {\n"
                . "    \$object->" . $property . " = \$value;\n"
                . "}, null, " . var_export($className, true) . ");";
        }

        $parser = new PHPParser_Parser(new PHPParser_Lexer());

        $method->stmts = $parser->parse('<?php ' . implode("\n", $bodyParts));
    }

    private function replaceHydrate(PHPParser_Node_Stmt_ClassMethod $method = null)
    {

    }

    private function replaceExtract(PHPParser_Node_Stmt_ClassMethod $method = null)
    {

    }

    private function findOrCreateMethod(PHPParser_Node_Stmt_Class $class, $name)
    {
        $foundMethods = array_filter(
            $class->getMethods(),
            function (PHPParser_Node_Stmt_ClassMethod $method) use ($name) {
                return $name === $method->name;
            }
        );

        $method = reset($foundMethods);

        if (!$method) {
            $class->stmts[] = $method = new PHPParser_Node_Stmt_ClassMethod($name);
        }

        return $method;
    }
}