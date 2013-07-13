<?php

namespace GeneratedHydratorTest\CodeGenerator\Visitor;

use GeneratedHydrator\CodeGenerator\Visitor\MethodDisablerVisitor;
use PHPParser_Node_Name;
use PHPParser_Node_Stmt_Class;
use PHPParser_Node_Stmt_ClassMethod;
use PHPParser_Node_Stmt_Namespace;
use PHPUnit_Framework_TestCase;

/**
 * @group CodeGeneration
 */
class MethodDisablerVisitorTest extends PHPUnit_Framework_TestCase
{
    public function testDisablesMethod()
    {
        $method = new PHPParser_Node_Stmt_ClassMethod('test');
        $filter = $this->getMock('stdClass', array('__invoke'));

        $filter->expects($this->once())->method('__invoke')->with($method)->will($this->returnValue(true));

        $visitor = new MethodDisablerVisitor($filter);

        $this->assertSame($method, $visitor->enterNode($method));
        $this->assertInstanceOf('PHPParser_Node_Stmt_Throw', reset($method->stmts));
    }

    public function testSkipsOnFailedFiltering()
    {
        $method = new PHPParser_Node_Stmt_ClassMethod('test');
        $filter = $this->getMock('stdClass', array('__invoke'));

        $filter->expects($this->once())->method('__invoke')->with($method)->will($this->returnValue(false));

        $visitor = new MethodDisablerVisitor($filter);

        $this->assertSame(null, $visitor->enterNode($method));
    }

    public function testSkipsOnNodeTypeMismatch()
    {
        $class  = new PHPParser_Node_Stmt_Class('test');
        $filter = $this->getMock('stdClass', array('__invoke'));

        $filter->expects($this->never())->method('__invoke');

        $visitor = new MethodDisablerVisitor($filter);

        $this->assertSame(null, $visitor->enterNode($class));
    }
}
