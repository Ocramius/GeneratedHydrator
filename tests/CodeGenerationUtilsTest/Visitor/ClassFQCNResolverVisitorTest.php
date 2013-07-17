<?php

namespace CodeGenerationUtilsTest\Visitor;

use CodeGenerationUtils\Visitor\ClassClonerVisitor;
use CodeGenerationUtils\Visitor\ClassFQCNResolverVisitor;
use PHPParser_Node_Name;
use PHPParser_Node_Stmt_Class;
use PHPParser_Node_Stmt_Namespace;
use PHPUnit_Framework_TestCase;
use ReflectionClass;

/**
 * @covers \CodeGenerationUtils\Visitor\ClassFQCNResolverVisitor
 */
class ClassFQCNResolverVisitorTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var ClassFQCNResolverVisitor
     */
    protected $visitor;

    /**
     * {@inheritDoc}
     */
    public function setUp()
    {
        $this->visitor = new ClassFQCNResolverVisitor();
    }

    public function testDiscoversSimpleClass()
    {
        $class = new PHPParser_Node_Stmt_Class('Foo');

        $this->visitor->beforeTraverse(array($class));
        $this->visitor->enterNode($class);

        $this->assertSame('Foo', $this->visitor->getName());
        $this->assertSame('', $this->visitor->getNamespace());
    }

    public function testDiscoversNamespacedClass()
    {
        $namespace = new PHPParser_Node_Stmt_Namespace(new PHPParser_Node_Name(array('Bar', 'Baz')));
        $class     = new PHPParser_Node_Stmt_Class('Foo');

        $namespace->stmts = array($class);

        $this->visitor->beforeTraverse(array($namespace));
        $this->visitor->enterNode($namespace);
        $this->visitor->enterNode($class);

        $this->assertSame('Foo', $this->visitor->getName());
        $this->assertSame('Bar\\Baz', $this->visitor->getNamespace());
    }

    public function testThrowsExceptionOnMultipleClasses()
    {
        $class1 = new PHPParser_Node_Stmt_Class('Foo');
        $class2 = new PHPParser_Node_Stmt_Class('Bar');

        $this->visitor->beforeTraverse(array($class1, $class2));

        $this->visitor->enterNode($class1);

        $this->setExpectedException('CodeGenerationUtils\Visitor\Exception\UnexpectedValueException');

        $this->visitor->enterNode($class2);
    }

    public function testThrowsExceptionOnMultipleNamespaces()
    {
        $namespace1 = new PHPParser_Node_Stmt_Namespace(new PHPParser_Node_Name('Foo'));
        $namespace2 = new PHPParser_Node_Stmt_Namespace(new PHPParser_Node_Name('Bar'));

        $this->visitor->beforeTraverse(array($namespace1, $namespace2));

        $this->visitor->enterNode($namespace1);

        $this->setExpectedException('CodeGenerationUtils\Visitor\Exception\UnexpectedValueException');

        $this->visitor->enterNode($namespace2);
    }

    public function testThrowsExceptionWhenNoClassIsFound()
    {
        $this->assertSame('', $this->visitor->getNamespace());

        $this->setExpectedException('CodeGenerationUtils\Visitor\Exception\UnexpectedValueException');

        $this->visitor->getName();
    }
}
