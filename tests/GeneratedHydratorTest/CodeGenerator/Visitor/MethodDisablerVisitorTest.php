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
    public function testDisablesMethodWhenFiltering()
    {
        $visitor = new MethodDisablerVisitor(
            function () {
                return true;
            }
        );

        $method = new PHPParser_Node_Stmt_ClassMethod('test');

        $this->assertSame($method, $visitor->enterNode($method));
        $this->assertInstanceOf('PHPParser_Node_Stmt_Throw', reset($method->stmts));
    }
}
