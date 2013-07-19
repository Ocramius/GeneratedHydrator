<?php

namespace GeneratedHydrator\CodeGenerator\Visitor;

use GeneratedHydrator\ClassGenerator\Hydrator\PropertyGenerator\PropertyAccessor;
use PHPParser_Lexer;
use PHPParser_Node;
use PHPParser_Node_Param;
use PHPParser_Node_Stmt_Class;
use PHPParser_Node_Stmt_ClassMethod;
use PHPParser_NodeVisitorAbstract;
use PHPParser_Parser;
use ReflectionClass;
use ReflectionProperty;

class HydratorMethodsVisitor extends PHPParser_NodeVisitorAbstract
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
        $this->accessibleProperties = $this->reflectedClass->getProperties(
            (ReflectionProperty::IS_PROTECTED | ReflectionProperty::IS_PUBLIC)
            &  ~ReflectionProperty::IS_STATIC
        );


        // @todo over-simplified for testing
        foreach ($reflectedClass->getProperties(ReflectionProperty::IS_PRIVATE) as $property) {
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
        $method->params = array(
            new PHPParser_Node_Param('data', null, 'array'),
            new PHPParser_Node_Param('object'),
        );

        // @todo check this...
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
                . $propertyWriter->getName()
                . '->__invoke($object, $data['
                . var_export($propertyWriter->getOriginalProperty()->getName(), true)
                . "]);\n";
        }

        $body .= "\nreturn \$object;";

        $parser = new PHPParser_Parser(new PHPParser_Lexer());

        $method->stmts = $parser->parse('<?php ' . $body);
    }

    private function replaceExtract(PHPParser_Node_Stmt_ClassMethod $method = null)
    {
        $parser = new PHPParser_Parser(new PHPParser_Lexer());

        $method->params = array(new PHPParser_Node_Param('object'));

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
