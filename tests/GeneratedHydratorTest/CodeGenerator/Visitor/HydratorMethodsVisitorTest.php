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
use PhpParser\Lexer;
use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Parser;
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
     * @param string   $className
     * @param Class_   $classNode
     * @param string[] $properties
     */
    public function testBasicCodeGeneration($className, Class_ $classNode, array $properties)
    {
        $visitor = new HydratorMethodsVisitor(new ReflectionClass($className));

        /* @var $modifiedAst Class_ */
        $modifiedNode = $visitor->leaveNode($classNode);

        $this->assertMethodExistence('hydrate', $modifiedNode);
        $this->assertMethodExistence('extract', $modifiedNode);
        $this->assertMethodExistence('__construct', $modifiedNode);
        $this->assertContainsPropertyAccessors($modifiedNode, $properties);
    }

    /**
     * Verifies that a method was correctly added to by the visitor
     *
     * @param string $methodName
     * @param Class_ $class
     */
    private function assertMethodExistence($methodName, Class_ $class)
    {
        $members = $class->stmts;

        $this->assertCount(
            1,
            array_filter(
                $members,
                function (Node $node) use ($methodName) {
                    return $node instanceof ClassMethod
                        && $methodName === $node->name;
                }
            )
        );
    }

    /**
     * Verifies that the given properties and only the given properties are added to the hydrator logic
     *
     * @param Class_   $class
     * @param string[] $properties
     */
    private function assertContainsPropertyAccessors(Class_ $class, array $properties)
    {
        $lookupProperties = array_flip($properties);

        foreach ($class->stmts as $method) {
            if ($method instanceof ClassMethod && $method->name === 'hydrate') {
                foreach ($method->stmts as $assignment) {
                    if ($assignment instanceof Assign) {
                        $var = $assignment->var;

                        if ($var instanceof PropertyFetch && is_string($var->name)) {
                            if (! isset($lookupProperties[$var->name])) {
                                $this->fail(sprintf('Property "%s" should not be hydrated', $var->name));
                            }

                            unset($lookupProperties[$var->name]);
                        }
                    }
                }
            }

            if ($method instanceof ClassMethod && $method->name === '__construct') {
                foreach ($method->stmts as $assignment) {
                    if ($assignment instanceof Assign) {
                        $var = $assignment->var;

                        if ($var instanceof PropertyFetch
                            && preg_match('/(.*)Writer[a-zA-Z0-9]+/', $assignment->var->name, $matches)
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
     * @return \PhpParser\Node[][]
     */
    public function classAstProvider()
    {
        $parser = new Parser(new Lexer());

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
