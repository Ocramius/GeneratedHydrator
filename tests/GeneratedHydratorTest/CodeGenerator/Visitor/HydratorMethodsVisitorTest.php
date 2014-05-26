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
use PHPParser_Node_Stmt_Class;
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
     * @param string[]                  $properties
     */
    public function testBasicCodeGeneration($className, PHPParser_Node_Stmt_Class $classNode, array $properties)
    {
        $visitor = new HydratorMethodsVisitor(new ReflectionClass($className));

        /* @var $modifiedAst PHPParser_Node_Stmt_Class */
        $modifiedNode = $visitor->leaveNode($classNode);

        $this->assertMethodExistence('hydrate', $modifiedNode);
        $this->assertMethodExistence('extract', $modifiedNode);
        $this->assertMethodExistence('__construct', $modifiedNode);
        $this->assertContainsPropertyAccessors($modifiedNode, $properties);
    }

    /**
     * Tests the Allowed Properties option.
     *
     * BaseClass has a public, protected and private property. Normally
     * `accessibleProperties` would have 2 properties (public & protected) and
     * `propertyWriters` just one (private).
     */
    public function testAllowPropertiesOption() {
        $visitor = new HydratorMethodsVisitor(
            new ReflectionClass('GeneratedHydratorTestAsset\\BaseClass'),
            array(HydratorMethodsVisitor::OPTION_ALLOWED_PROPERTIES => array(
                'protectedProperty'
            ))
        );

        $reflClass = new ReflectionClass($visitor);
        $reflProperty1 = $reflClass->getProperty('accessibleProperties');
        $reflProperty1->setAccessible(true);
        $reflProperty2 = $reflClass->getProperty('propertyWriters');
        $reflProperty2->setAccessible(true);

        $this->assertCount(1, $reflProperty1->getValue($visitor));
        $this->assertCount(0, $reflProperty2->getValue($visitor));
    }

    /**
     * Verifies that a method was correctly added to by the visitor
     *
     * @param string                    $methodName
     * @param PHPParser_Node_Stmt_Class $class
     */
    private function assertMethodExistence($methodName, PHPParser_Node_Stmt_Class $class)
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
     * Verifies that the given properties and only the given properties are added to the hydrator logic
     *
     * @param PHPParser_Node_Stmt_Class $class
     * @param array                     $properties
     */
    private function assertContainsPropertyAccessors(PHPParser_Node_Stmt_Class $class, array $properties)
    {
        $lookupProperties = array_flip($properties);

        foreach ($class->stmts as $method) {
            if ($method instanceof \PHPParser_Node_Stmt_ClassMethod && $method->name === 'hydrate') {
                foreach ($method->stmts as $assignment) {
                    if ($assignment instanceof \PHPParser_Node_Expr_Assign) {
                        $var = $assignment->var;

                        if ($var instanceof \PHPParser_Node_Expr_PropertyFetch && is_string($var->name)) {
                            if (! isset($lookupProperties[$var->name])) {
                                $this->fail(sprintf('Property "%s" should not be hydrated', $var->name));
                            }

                            unset($lookupProperties[$var->name]);
                        }
                    }
                }
            }

            if ($method instanceof \PHPParser_Node_Stmt_ClassMethod && $method->name === '__construct') {
                foreach ($method->stmts as $assignment) {
                    if ($assignment instanceof \PHPParser_Node_Expr_Assign) {
                        $var = $assignment->var;

                        if ($var instanceof \PHPParser_Node_Expr_PropertyFetch
                            && preg_match('/(.*)Writer[a-zA-Z0-9]+/', $var->name, $matches)
                        ) {
                            if (! isset($lookupProperties[$matches[1]])) {
                                $this->fail(sprintf('Property "%s" should not be hydrated', $matches[1]));
                            }

                            unset($lookupProperties[$matches[1]]);
                        }
                    }
                }
            }
        }

        if (empty($lookupProperties)) {
            return;
        }

        $this->fail(sprintf(
            'Could not match following properties in the generated code: %s',
            var_export(array_flip($lookupProperties), true)
        ));
    }

    /**
     * @return \PHPParser_Node[][]
     */
    public function classAstProvider()
    {
        $parser = new PHPParser_Parser(new PHPParser_Lexer());

        $className = UniqueIdentifierGenerator::getIdentifier('Foo');
        $classCode = 'class ' . $className . ' { private $bar; private $baz; protected $tab; '
            . 'protected $tar; public $taw; public $tam; }';

        eval($classCode);

        $staticClassName = UniqueIdentifierGenerator::getIdentifier('Foo');
        $staticClassCode = 'class ' . $staticClassName . ' { private static $bar; '
            . 'protected static $baz; public static $tab; private $taz; }';

        eval($staticClassCode);

        return [
            [$className, $parser->parse('<?php ' . $classCode)[0], ['bar', 'baz', 'tab', 'tar', 'taw', 'tam']],
            [$staticClassName, $parser->parse('<?php ' . $staticClassCode)[0], ['taz']],
        ];
    }
}
