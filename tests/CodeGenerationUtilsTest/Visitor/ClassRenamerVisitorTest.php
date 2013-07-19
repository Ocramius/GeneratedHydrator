<?php

namespace CodeGenerationUtilsTest\Visitor;

use CodeGenerationUtils\Visitor\ClassRenamerVisitor;
use PHPParser_Node_Name;
use PHPParser_Node_Stmt_Class;
use PHPParser_Node_Stmt_Namespace;
use PHPUnit_Framework_TestCase;
use ReflectionClass;

/**
 * @covers \CodeGenerationUtils\Visitor\ClassRenamerVisitor
 */
class ClassRenamerVisitorTest extends PHPUnit_Framework_TestCase
{
    public function testRenamesNodesOnMatchingClass()
    {
        $visitor   = new ClassRenamerVisitor(new ReflectionClass(__CLASS__), 'Foo\\Bar\\Baz');
        $class     = new PHPParser_Node_Stmt_Class('ClassRenamerVisitorTest');
        $namespace = new PHPParser_Node_Stmt_Namespace(
            new PHPParser_Node_Name(array('CodeGenerationUtilsTest', 'Visitor'))
        );

        $visitor->beforeTraverse(array());
        $this->assertSame($namespace, $visitor->enterNode($namespace));
        $this->assertNull($visitor->enterNode($class));
        $this->assertSame($class, $visitor->leaveNode($class));
        $this->assertSame($namespace, $visitor->leaveNode($namespace));

        $this->assertSame('Baz', $class->name);
        $this->assertSame(array('Foo', 'Bar'), $namespace->name->parts);
        $this->assertSame(array($class), $namespace->stmts);
    }

    public function testIgnoresNodesOnNonMatchingClass()
    {
        $visitor   = new ClassRenamerVisitor(new ReflectionClass(__CLASS__), 'Foo\\Bar\\Baz');
        $class     = new PHPParser_Node_Stmt_Class('Wrong');
        $namespace = new PHPParser_Node_Stmt_Namespace(
            new PHPParser_Node_Name(array('CodeGenerationUtilsTest', 'Visitor'))
        );

        $visitor->beforeTraverse(array());
        $this->assertSame($namespace, $visitor->enterNode($namespace));
        $this->assertNull($visitor->enterNode($class));
        $visitor->leaveNode($class);
        $visitor->leaveNode($namespace);

        $this->assertSame('Wrong', $class->name);
        $this->assertSame(array('CodeGenerationUtilsTest', 'Visitor'), $namespace->name->parts);
    }

    public function testIgnoresNodesOnNonMatchingNamespace()
    {
        $visitor   = new ClassRenamerVisitor(new ReflectionClass(__CLASS__), 'Foo\\Bar\\Baz');
        $class     = new PHPParser_Node_Stmt_Class('ClassRenamerVisitorTest');
        $namespace = new PHPParser_Node_Stmt_Namespace(
            new PHPParser_Node_Name(array('Wrong', 'Namespace', 'Here'))
        );

        $visitor->beforeTraverse(array());
        $this->assertSame($namespace, $visitor->enterNode($namespace));
        $this->assertNull($visitor->enterNode($class));
        $visitor->leaveNode($class);
        $visitor->leaveNode($namespace);

        $this->assertSame('ClassRenamerVisitorTest', $class->name);
        $this->assertSame(array('Wrong', 'Namespace', 'Here'), $namespace->name->parts);
    }

    public function testMatchOnEmptyNamespace()
    {
        $visitor   = new ClassRenamerVisitor(new ReflectionClass('stdClass'), 'Baz');
        $class     = new PHPParser_Node_Stmt_Class('stdClass');

        $visitor->beforeTraverse(array());
        $this->assertNull($visitor->enterNode($class));
        $this->assertSame($class, $visitor->leaveNode($class));

        $this->assertSame('Baz', $class->name);
    }

    public function testUnwrapsNamespacedClassCorrectly()
    {
        $visitor   = new ClassRenamerVisitor(new ReflectionClass(__CLASS__), 'Baz');
        $class     = new PHPParser_Node_Stmt_Class('ClassRenamerVisitorTest');
        $namespace = new PHPParser_Node_Stmt_Namespace(
            new PHPParser_Node_Name(array('CodeGenerationUtilsTest', 'Visitor'))
        );

        $visitor->beforeTraverse(array());
        $this->assertSame($namespace, $visitor->enterNode($namespace));
        $this->assertNull($visitor->enterNode($class));
        $this->assertSame($class, $visitor->leaveNode($class));
        $this->assertSame(array($class), $visitor->leaveNode($namespace));

        $this->assertSame('Baz', $class->name);
    }

    public function testWrapsGlobalClassCorrectly()
    {
        $visitor   = new ClassRenamerVisitor(new ReflectionClass('stdClass'), 'Foo\\Bar');
        $class     = new PHPParser_Node_Stmt_Class('stdClass');

        $visitor->beforeTraverse(array());
        $this->assertNull($visitor->enterNode($class));
        $namespace = $visitor->leaveNode($class);

        $this->assertInstanceOf('PHPParser_Node_Stmt_Namespace', $namespace);
        $this->assertSame('Foo', $namespace->name->toString());
        $this->assertSame(array($class), $namespace->stmts);
    }

    public function testMismatchOnEmptyNamespace()
    {
        $visitor   = new ClassRenamerVisitor(new ReflectionClass('stdClass'), 'Baz');
        $class     = new PHPParser_Node_Stmt_Class('stdClass');
        $namespace = new PHPParser_Node_Stmt_Namespace(
            new PHPParser_Node_Name(array('Wrong', 'Namespace', 'Here'))
        );

        $visitor->beforeTraverse(array());
        $this->assertSame($namespace, $visitor->enterNode($namespace));
        $this->assertNull($visitor->enterNode($class));
        $visitor->leaveNode($class);
        $visitor->leaveNode($namespace);

        $this->assertSame('stdClass', $class->name);
        $this->assertSame(array('Wrong', 'Namespace', 'Here'), $namespace->name->parts);
    }
}
