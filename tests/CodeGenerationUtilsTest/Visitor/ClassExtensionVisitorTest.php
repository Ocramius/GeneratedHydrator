<?php

namespace CodeGenerationUtilsTest\Visitor;

use CodeGenerationUtils\Visitor\ClassExtensionVisitor;
use PHPParser_Node_Name;
use PHPParser_Node_Stmt_Class;
use PHPParser_Node_Stmt_Namespace;
use PHPUnit_Framework_TestCase;

/**
 * @covers \CodeGenerationUtils\Visitor\ClassExtensionVisitor
 */
class ClassExtensionVisitorTest extends PHPUnit_Framework_TestCase
{
    public function testRenamesNodesOnMatchingClass()
    {
        $visitor   = new ClassExtensionVisitor('Foo\\Bar', 'Baz\\Tab');
        $class     = new PHPParser_Node_Stmt_Class('Bar');
        $namespace = new PHPParser_Node_Stmt_Namespace(new PHPParser_Node_Name('Foo'));

        $visitor->beforeTraverse(array());
        $this->assertSame($namespace, $visitor->enterNode($namespace));
        $this->assertNull($visitor->enterNode($class));
        $this->assertSame($class, $visitor->leaveNode($class));
        $this->assertNull($visitor->leaveNode($namespace));

        $this->assertNotNull($class->extends);
        $this->assertSame('Baz\\Tab', $class->extends->toString());
    }

    public function testIgnoresNodesOnNonMatchingClass()
    {
        $visitor   = new ClassExtensionVisitor('Foo\\Bar', 'Baz\\Tab');
        $class     = new PHPParser_Node_Stmt_Class('Tab');
        $namespace = new PHPParser_Node_Stmt_Namespace(new PHPParser_Node_Name('Foo'));

        $visitor->beforeTraverse(array());
        $this->assertSame($namespace, $visitor->enterNode($namespace));
        $this->assertNull($visitor->enterNode($class));
        $this->assertSame($class, $visitor->leaveNode($class));
        $this->assertNull($visitor->leaveNode($namespace));

        $this->assertNull($class->extends);
    }

    public function testIgnoresNodesOnNonMatchingNamespace()
    {
        $visitor   = new ClassExtensionVisitor('Foo\\Bar', 'Baz\\Tab');
        $class     = new PHPParser_Node_Stmt_Class('Bar');
        $namespace = new PHPParser_Node_Stmt_Namespace(new PHPParser_Node_Name('Tab'));

        $visitor->beforeTraverse(array());
        $this->assertSame($namespace, $visitor->enterNode($namespace));
        $this->assertNull($visitor->enterNode($class));
        $this->assertSame($class, $visitor->leaveNode($class));
        $this->assertNull($visitor->leaveNode($namespace));

        $this->assertNull($class->extends);
    }

    public function testMatchOnEmptyNamespace()
    {
        $visitor   = new ClassExtensionVisitor('Foo', 'Baz\\Tab');
        $class     = new PHPParser_Node_Stmt_Class('Foo');

        $visitor->beforeTraverse(array());
        $this->assertNull($visitor->enterNode($class));
        $this->assertSame($class, $visitor->leaveNode($class));

        $this->assertNotNull($class->extends);
        $this->assertSame('Baz\\Tab', $class->extends->toString());
    }
}
