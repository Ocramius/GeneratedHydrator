<?php

namespace GeneratedHydratorTest\CodeGenerator\Visitor;

use GeneratedHydrator\CodeGenerator\Visitor\ClassClonerVisitor;
use GeneratedHydrator\CodeGenerator\Visitor\ClassRenamerVisitor;
use PHPParser_Builder_Class;
use PHPParser_Node_Name;
use PHPParser_Node_Stmt_Class;
use PHPParser_Node_Stmt_Namespace;
use PHPParser_NodeTraverser;
use PHPUnit_Framework_TestCase;
use ReflectionClass;

/**
 * @group CodeGeneration
 */
class ClassRenamerVisitorTest extends PHPUnit_Framework_TestCase
{
    public function testRenamesNodesOnMatchingClass()
    {
        $reflectionClass = new ReflectionClass(__CLASS__);
        $visitor         = new ClassRenamerVisitor($reflectionClass, 'Foo\\Bar\\Baz');
        $class           = new PHPParser_Node_Stmt_Class('ClassRenamerVisitorTest');
        $namespace       = new PHPParser_Node_Stmt_Namespace(
            new PHPParser_Node_Name(array('GeneratedHydratorTest', 'CodeGenerator', 'Visitor'))
        );

        $this->assertSame($namespace, $visitor->enterNode($namespace));
        $this->assertSame($class, $visitor->enterNode($class));
        $visitor->leaveNode($class);
        $visitor->leaveNode($namespace);

        $this->assertSame(array('Foo', 'Bar'), $namespace->name->parts);
        $this->assertSame('Baz', $class->name);
    }

    public function testIgnoresNodesOnNonMatchingClass()
    {
        $this->markTestIncomplete();
    }

    public function testIgnoresNodesOnNonMatchingVisitor()
    {
        $this->markTestIncomplete();
    }
}
