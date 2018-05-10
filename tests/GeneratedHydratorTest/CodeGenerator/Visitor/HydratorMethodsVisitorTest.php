<?php

declare(strict_types=1);

namespace GeneratedHydratorTest\CodeGenerator\Visitor;

use GeneratedHydrator\CodeGenerator\Visitor\HydratorMethodsVisitor;
use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\ParserFactory;
use PHPUnit\Framework\TestCase;
use CodeGenerationUtils\Inflector\Util\UniqueIdentifierGenerator;
use ReflectionClass;

/**
 * Tests for {@see \GeneratedHydrator\CodeGenerator\Visitor\HydratorMethodsVisitor}
 *
 * @covers \GeneratedHydrator\CodeGenerator\Visitor\HydratorMethodsVisitor
 */
class HydratorMethodsVisitorTest extends TestCase
{
    /**
     * @dataProvider classAstProvider
     *
     * @param string   $className
     * @param Class_   $classNode
     * @param string[] $properties
     */
    public function testBasicCodeGeneration(string $className, Class_ $classNode, array $properties)
    {
        $visitor = new HydratorMethodsVisitor(new ReflectionClass($className));

        /* @var $modifiedAst Class_ */
        $modifiedNode = $visitor->leaveNode($classNode);

        self::assertMethodExistence('hydrate', $modifiedNode);
        self::assertMethodExistence('extract', $modifiedNode);
        self::assertMethodExistence('__construct', $modifiedNode);
    }

    /**
     * Verifies that a method was correctly added to by the visitor
     *
     * @param string $methodName
     * @param Class_ $class
     */
    private function assertMethodExistence(string $methodName, Class_ $class)
    {
        $members = $class->stmts;

        self::assertCount(
            1,
            array_filter(
                $members,
                function (Node $node) use ($methodName) : bool {
                    return $node instanceof ClassMethod
                        && $methodName === $node->name;
                }
            )
        );
    }

    /**
     * @return \PhpParser\Node[][]
     */
    public function classAstProvider() : array
    {
        $parser = (new ParserFactory())->create(ParserFactory::ONLY_PHP7);

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
