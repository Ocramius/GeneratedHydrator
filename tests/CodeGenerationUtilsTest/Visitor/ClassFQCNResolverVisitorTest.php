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
use CodeGenerationUtils\Visitor\ClassFQCNResolverVisitor;
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
 * @covers \CodeGenerationUtils\Visitor\ClassFQCNResolverVisitor
 */
class ClassFQCNResolverVisitorTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var ClassFQCNResolverVisitor
     */
    protected $visitor;

    /**
     * {@inheritDoc}
     */
    public function setUp()
    {
        $this->visitor = new ClassFQCNResolverVisitor();
    }

    public function testDiscoversSimpleClass()
    {
        $class = new PHPParser_Node_Stmt_Class('Foo');

        $this->visitor->beforeTraverse(array($class));
        $this->visitor->enterNode($class);

        $this->assertSame('Foo', $this->visitor->getName());
        $this->assertSame('', $this->visitor->getNamespace());
    }

    public function testDiscoversNamespacedClass()
    {
        $namespace = new PHPParser_Node_Stmt_Namespace(new PHPParser_Node_Name(array('Bar', 'Baz')));
        $class     = new PHPParser_Node_Stmt_Class('Foo');

        $namespace->stmts = array($class);

        $this->visitor->beforeTraverse(array($namespace));
        $this->visitor->enterNode($namespace);
        $this->visitor->enterNode($class);

        $this->assertSame('Foo', $this->visitor->getName());
        $this->assertSame('Bar\\Baz', $this->visitor->getNamespace());
    }

    public function testThrowsExceptionOnMultipleClasses()
    {
        $class1 = new PHPParser_Node_Stmt_Class('Foo');
        $class2 = new PHPParser_Node_Stmt_Class('Bar');

        $this->visitor->beforeTraverse(array($class1, $class2));

        $this->visitor->enterNode($class1);

        $this->setExpectedException('CodeGenerationUtils\Visitor\Exception\UnexpectedValueException');

        $this->visitor->enterNode($class2);
    }

    public function testThrowsExceptionOnMultipleNamespaces()
    {
        $namespace1 = new PHPParser_Node_Stmt_Namespace(new PHPParser_Node_Name('Foo'));
        $namespace2 = new PHPParser_Node_Stmt_Namespace(new PHPParser_Node_Name('Bar'));

        $this->visitor->beforeTraverse(array($namespace1, $namespace2));

        $this->visitor->enterNode($namespace1);

        $this->setExpectedException('CodeGenerationUtils\Visitor\Exception\UnexpectedValueException');

        $this->visitor->enterNode($namespace2);
    }

    public function testThrowsExceptionWhenNoClassIsFound()
    {
        $this->assertSame('', $this->visitor->getNamespace());

        $this->setExpectedException('CodeGenerationUtils\Visitor\Exception\UnexpectedValueException');

        $this->visitor->getName();
    }
}
