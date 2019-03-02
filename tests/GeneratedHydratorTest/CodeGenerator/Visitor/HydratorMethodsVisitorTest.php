<?php

declare(strict_types=1);

namespace GeneratedHydratorTest\CodeGenerator\Visitor;

use CodeGenerationUtils\Inflector\Util\UniqueIdentifierGenerator;
use GeneratedHydrator\CodeGenerator\Visitor\HydratorMethodsVisitor;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\ParserFactory;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use function array_filter;

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
     * @param string[] $properties
     */
    public function testBasicCodeGeneration(string $className, Class_ $classNode, array $properties) : void
    {
        $visitor = new HydratorMethodsVisitor(new ReflectionClass($className));

        /** @var Class_ $modifiedAst */
        $modifiedNode = $visitor->leaveNode($classNode);

        self::assertMethodExistence('hydrate', $modifiedNode);
        self::assertMethodExistence('extract', $modifiedNode);
        self::assertMethodExistence('__construct', $modifiedNode);
    }

    /**
     * Verifies that a method was correctly added to by the visitor
     */
    private function assertMethodExistence(string $methodName, Class_ $class) : void
    {
        $members = $class->stmts;

        self::assertCount(
            1,
            array_filter(
                $members,
                function (Node $node) use ($methodName) : bool {
                    return $node instanceof ClassMethod
                        && $methodName === $node->name->name;
                }
            )
        );
    }

    /**
     * @return Node[]
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
