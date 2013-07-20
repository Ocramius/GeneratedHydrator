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

use CodeGenerationUtils\Visitor\ClassExtensionVisitor;
use PHPParser_Node_Name;
use PHPParser_Node_Stmt_Class;
use PHPParser_Node_Stmt_Namespace;
use PHPUnit_Framework_TestCase;

/**
 * Tests for {@see \CodeGenerationUtils\Visitor\ClassClonerVisitor}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 *
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
