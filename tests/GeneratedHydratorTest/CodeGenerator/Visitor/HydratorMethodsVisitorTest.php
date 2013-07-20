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

namespace GeneratedHydratorTest\CodeGenerator\Visitor;

use GeneratedHydrator\CodeGenerator\Visitor\HydratorMethodsVisitor;
use PHPParser_Lexer;
use PHPParser_Node;
use PHPParser_Node_Name;
use PHPParser_Node_Stmt_Class;
use PHPParser_Node_Stmt_Namespace;
use PHPParser_Parser;
use PHPUnit_Framework_TestCase;
use CodeGenerationUtils\Inflector\Util\UniqueIdentifierGenerator;
use ReflectionClass;

/**
 * Tests for {@see \GeneratedHydrator\CodeGenerator\Visitor\HydratorMethodsVisitor}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 *
 * @covers \GeneratedHydrator\CodeGenerator\Visitor\HydratorMethodsVisitor
 */
class HydratorMethodsVisitorTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider classAstProvider
     *
     * @param string                    $className
     * @param PHPParser_Node_Stmt_Class $classNode
     */
    public function testBasicCodeGeneration($className, PHPParser_Node_Stmt_Class $classNode)
    {
        $visitor = new HydratorMethodsVisitor(new ReflectionClass($className));

        /* @var $modifiedAst PHPParser_Node_Stmt_Class */
        $modifiedNode = $visitor->leaveNode($classNode);

        $this->checkMethodExistence('hydrate', $modifiedNode);
        $this->checkMethodExistence('extract', $modifiedNode);
        $this->checkMethodExistence('__construct', $modifiedNode);
    }

    /**
     * Verifies that a method was correctly added to by the visitor
     *
     * @param string                    $methodName
     * @param PHPParser_Node_Stmt_Class $class
     */
    private function checkMethodExistence($methodName, PHPParser_Node_Stmt_Class $class)
    {

        $members = $class->stmts;

        $this->assertCount(
            1,
            array_filter(
                $members,
                function (PHPParser_Node $node) use ($methodName) {
                    return $node instanceof \PHPParser_Node_Stmt_ClassMethod
                        && $methodName === $node->name;
                }
            )
        );
    }

    /**
     * @return \PHPParser_Node[][]
     */
    public function classAstProvider()
    {
        $parser = new PHPParser_Parser(new PHPParser_Lexer());

        $className = UniqueIdentifierGenerator::getIdentifier('Foo');
        $classCode = 'class ' . $className . ' { private $bar; private $baz; protected $tab;'
            . 'protected $tar; public $taw; public $tam; }';

        eval($classCode);

        return array(
            array($className, $parser->parse('<?php ' . $classCode)[0]),
        );
    }
}
