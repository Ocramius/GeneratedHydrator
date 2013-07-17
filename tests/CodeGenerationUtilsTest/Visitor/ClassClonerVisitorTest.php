<?php

namespace CodeGenerationUtilsTest\Visitor;

use CodeGenerationUtils\Visitor\ClassClonerVisitor;
use PHPUnit_Framework_TestCase;
use ReflectionClass;

/**
 * @group CodeGeneration
 */
class ClassClonerVisitorTest extends PHPUnit_Framework_TestCase
{
    public function testClonesClassIntoEmptyNodeList()
    {
        $reflectionClass = new ReflectionClass(__CLASS__);

        $visitor = new ClassClonerVisitor($reflectionClass);

        $nodes = $visitor->beforeTraverse(array());

        $this->assertInstanceOf('PHPParser_Node_Stmt_Namespace', $nodes[0]);

        /* @var $node \PHPParser_Node_Stmt_Namespace */
        $node = $nodes[0];

        $this->assertSame(__NAMESPACE__, implode('\\', $node->name->parts));

        /* @var $class \PHPParser_Node_Stmt_Class */
        $class = end($node->stmts);

        $this->assertInstanceOf('PHPParser_Node_Stmt_Class', $class);
        $this->assertSame('ClassClonerVisitorTest', $class->name);
    }

    public function testClonesClassIntoNonEmptyNodeList()
    {
        $this->markTestIncomplete('Still not clear thoughts on this...');
    }
}
