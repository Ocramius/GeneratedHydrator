<?php

namespace CodeGenerationUtilsTest\Visitor;

use CodeGenerationUtils\Visitor\ClassImplementorVisitor;
use PHPParser_Node_Name;
use PHPParser_Node_Stmt_Class;
use PHPParser_Node_Stmt_Namespace;
use PHPUnit_Framework_TestCase;

/**
 * @covers \CodeGenerationUtils\Visitor\ClassImplementorVisitor
 */
class ClassImplementorVisitorTest extends PHPUnit_Framework_TestCase
{
    public function testRenamesNodesOnMatchingClass()
    {
        $visitor   = new ClassImplementorVisitor('Foo\\Bar', array('Baz\\Tab', 'Tar\\War'));
        $class     = new PHPParser_Node_Stmt_Class('Bar');
        $namespace = new PHPParser_Node_Stmt_Namespace(new PHPParser_Node_Name('Foo'));

        $visitor->beforeTraverse(array());
        $this->assertSame($namespace, $visitor->enterNode($namespace));
        $this->assertNull($visitor->enterNode($class));
        $this->assertSame($class, $visitor->leaveNode($class));
        $this->assertNull($visitor->leaveNode($namespace));

        $this->assertSame('Baz\\Tab', $class->implements[0]->toString());
        $this->assertSame('Tar\\War', $class->implements[1]->toString());
    }

    public function testIgnoresNodesOnNonMatchingClass()
    {
        $visitor   = new ClassImplementorVisitor('Foo\\Bar', array('Baz\\Tab', 'Tar\\War'));
        $class     = new PHPParser_Node_Stmt_Class('Tab');
        $namespace = new PHPParser_Node_Stmt_Namespace(new PHPParser_Node_Name('Foo'));

        $visitor->beforeTraverse(array());
        $this->assertSame($namespace, $visitor->enterNode($namespace));
        $this->assertNull($visitor->enterNode($class));
        $this->assertSame($class, $visitor->leaveNode($class));
        $this->assertNull($visitor->leaveNode($namespace));

        $this->assertEmpty($class->extends);
    }

    public function testIgnoresNodesOnNonMatchingNamespace()
    {
        $visitor   = new ClassImplementorVisitor('Foo\\Bar', array('Baz\\Tab', 'Tar\\War'));
        $class     = new PHPParser_Node_Stmt_Class('Bar');
        $namespace = new PHPParser_Node_Stmt_Namespace(new PHPParser_Node_Name('Tab'));

        $visitor->beforeTraverse(array());
        $this->assertSame($namespace, $visitor->enterNode($namespace));
        $this->assertNull($visitor->enterNode($class));
        $this->assertSame($class, $visitor->leaveNode($class));
        $this->assertNull($visitor->leaveNode($namespace));

        $this->assertEmpty($class->extends);
    }

    public function testMatchOnEmptyNamespace()
    {
        $visitor   = new ClassImplementorVisitor('Foo', array('Baz\\Tab', 'Tar\\War'));
        $class     = new PHPParser_Node_Stmt_Class('Foo');

        $visitor->beforeTraverse(array());
        $this->assertNull($visitor->enterNode($class));
        $this->assertSame($class, $visitor->leaveNode($class));

        $this->assertSame('Baz\\Tab', $class->implements[0]->toString());
        $this->assertSame('Tar\\War', $class->implements[1]->toString());
    }
}
