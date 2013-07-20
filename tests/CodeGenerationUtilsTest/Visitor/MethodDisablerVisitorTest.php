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

use CodeGenerationUtils\Visitor\MethodDisablerVisitor;
use PHPParser_Node_Name;
use PHPParser_Node_Stmt_Class;
use PHPParser_Node_Stmt_ClassMethod;
use PHPParser_Node_Stmt_Namespace;
use PHPUnit_Framework_TestCase;

/**
 * Tests for {@see \CodeGenerationUtils\Visitor\ClassClonerVisitor}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 *
 * @covers \CodeGenerationUtils\Visitor\MethodDisablerVisitor
 */
class MethodDisablerVisitorTest extends PHPUnit_Framework_TestCase
{
    public function testDisablesMethod()
    {
        $method = new PHPParser_Node_Stmt_ClassMethod('test');
        $filter = $this->getMock('stdClass', array('__invoke'));

        $filter->expects($this->once())->method('__invoke')->with($method)->will($this->returnValue(true));

        $visitor = new MethodDisablerVisitor($filter);

        $this->assertSame($method, $visitor->leaveNode($method));
        $this->assertInstanceOf('PHPParser_Node_Stmt_Throw', reset($method->stmts));
    }

    public function testSkipsOnFailedFiltering()
    {
        $method = new PHPParser_Node_Stmt_ClassMethod('test');
        $filter = $this->getMock('stdClass', array('__invoke'));

        $filter->expects($this->once())->method('__invoke')->with($method)->will($this->returnValue(false));

        $visitor = new MethodDisablerVisitor($filter);

        $this->assertSame(false, $visitor->leaveNode($method));
    }

    public function testSkipsOnIgnoreFiltering()
    {
        $method = new PHPParser_Node_Stmt_ClassMethod('test');
        $filter = $this->getMock('stdClass', array('__invoke'));

        $filter->expects($this->once())->method('__invoke')->with($method)->will($this->returnValue(null));

        $visitor = new MethodDisablerVisitor($filter);

        $this->assertSame(null, $visitor->leaveNode($method));
    }

    public function testSkipsOnNodeTypeMismatch()
    {
        $class  = new PHPParser_Node_Stmt_Class('test');
        $filter = $this->getMock('stdClass', array('__invoke'));

        $filter->expects($this->never())->method('__invoke');

        $visitor = new MethodDisablerVisitor($filter);

        $this->assertSame(null, $visitor->leaveNode($class));
    }
}
