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

use CodeGenerationUtils\ReflectionBuilder\ClassBuilder;
use PHPParser_Node_Stmt_ClassMethod;
use PHPUnit_Framework_TestCase;
use ReflectionClass;

/**
 * Tests for {@see \CodeGenerationUtils\ReflectionBuilder\ClassBuilder}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 *
 * @covers \CodeGenerationUtils\ReflectionBuilder\ClassBuilder
 */
class ClassBuilderTest extends PHPUnit_Framework_TestCase
{
    /**
     * Simple test reflecting this test class
     */
    public function testBuildSelf()
    {
        $classBuilder = new ClassBuilder();
        $ast          = $classBuilder->fromReflection(new ReflectionClass(__CLASS__));
        /* @var $namespace \PHPParser_Node_Stmt_Namespace */
        $namespace    = $ast[0];

        $this->assertInstanceOf('PHPParser_Node_Stmt_Namespace', $namespace);
        $this->assertSame(__NAMESPACE__, $namespace->name->toString());

        /* @var $class \PHPParser_Node_Stmt_Class */
        $class = $namespace->stmts[0];

        $this->assertInstanceOf('PHPParser_Node_Stmt_Class', $class);
        $this->assertSame('ClassBuilderTest', $class->name);

        $currentMethod = __FUNCTION__;
        /* @var $methods PHPParser_Node_Stmt_ClassMethod[] */
        $methods       = array_filter(
            $class->stmts,
            function ($node) use ($currentMethod) {
                return $node instanceof PHPParser_Node_Stmt_ClassMethod && $node->name === $currentMethod;
            }
        );

        $this->assertCount(1, $methods);

        /* @var $thisMethod PHPParser_Node_Stmt_ClassMethod */
        $thisMethod = reset($methods);

        $this->assertSame($currentMethod, $thisMethod->name);
    }
}
