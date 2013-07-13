<?php

namespace GeneratedHydratorTest\CodeGenerator\Visitor;

use GeneratedHydrator\CodeGenerator\Visitor\ClassClonerVisitor;
use GeneratedHydrator\CodeGenerator\Visitor\ClassRenamerVisitor;
use GeneratedHydrator\CodeGenerator\Visitor\PublicMethodsFilterVisitor;
use PHPParser_Builder_Class;
use PHPParser_Node;
use PHPParser_Node_Name;
use PHPParser_Node_Stmt_Class;
use PHPParser_Node_Stmt_Namespace;
use PHPParser_NodeTraverser;
use PHPUnit_Framework_TestCase;
use ReflectionClass;

/**
 * @group CodeGeneration
 */
class PublicMethodsFilterVisitorTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider nodeProvider
     *
     * @param PHPParser_Node $node
     * @param mixed          $expected
     */
    public function testRemovesOnlyPrivateMethods(PHPParser_Node $node, $expected)
    {
        $visitor = new PublicMethodsFilterVisitor();

        $this->assertSame($expected, $visitor->leaveNode($node));
    }

    public function nodeProvider()
    {
        return array(
            array(
                new \PHPParser_Node_Stmt_ClassMethod(
                    'foo',
                    array('type' => PHPParser_Node_Stmt_Class::MODIFIER_PUBLIC)
                ),
                null,
            ),
            array(
                new \PHPParser_Node_Stmt_ClassMethod(
                    'foo',
                    array('type' => PHPParser_Node_Stmt_Class::MODIFIER_PROTECTED)
                ),
                false,
            ),
            array(
                new \PHPParser_Node_Stmt_ClassMethod(
                    'foo',
                    array('type' => PHPParser_Node_Stmt_Class::MODIFIER_PRIVATE)
                ),
                false,
            ),
            array(new \PHPParser_Node_Stmt_Class('foo'), null,),
        );
    }
}
