<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license.
 */

namespace CodeGenerationUtilsTest\Visitor;

use CodeGenerationUtils\Visitor\ClassClonerVisitor;
use PHPUnit_Framework_TestCase;
use ReflectionClass;

/**
 * Tests for {@see \CodeGenerationUtils\Visitor\ClassClonerVisitor}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 *
 * @covers \CodeGenerationUtils\Visitor\ClassClonerVisitor
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
