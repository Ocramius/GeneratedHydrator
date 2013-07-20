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

use CodeGenerationUtils\Visitor\ClassRenamerVisitor;
use PHPParser_Node_Name;
use PHPParser_Node_Stmt_Class;
use PHPParser_Node_Stmt_Namespace;
use PHPUnit_Framework_TestCase;
use ReflectionClass;

/**
 * Tests for {@see \CodeGenerationUtils\Visitor\ClassClonerVisitor}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 *
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
