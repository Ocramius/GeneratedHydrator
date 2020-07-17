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
use function assert;
use function class_exists;

/**
 * Tests for {@see \GeneratedHydrator\CodeGenerator\Visitor\HydratorMethodsVisitor}
 *
 * @covers \GeneratedHydrator\CodeGenerator\Visitor\HydratorMethodsVisitor
 */
class HydratorMethodsVisitorTest extends TestCase
{
    /**
     * @param string[] $properties
     *
     * @psalm-param class-string $className
     *
     * @dataProvider classAstProvider
     */
    public function testBasicCodeGeneration(string $className, Class_ $classNode, array $properties): void
    {
        $visitor = new HydratorMethodsVisitor(new ReflectionClass($className));

        $modifiedNode = $visitor->leaveNode($classNode);

        self::assertNotNull($modifiedNode);
        self::assertMethodExistence('hydrate', $modifiedNode);
        self::assertMethodExistence('extract', $modifiedNode);
        self::assertMethodExistence('__construct', $modifiedNode);
    }

    /**
     * Verifies that a method was correctly added to by the visitor
     */
    private function assertMethodExistence(string $methodName, Class_ $class): void
    {
        $members = $class->stmts;

        self::assertCount(
            1,
            array_filter(
                $members,
                static function (Node $node) use ($methodName): bool {
                    return $node instanceof ClassMethod
                        && $methodName === $node->name->name;
                }
            )
        );
    }

    /**
     * @psalm-return non-empty-list<array{
     *   class-string,
     *   Class_,
     *   non-empty-list<non-empty-string>
     * }>
     */
    public function classAstProvider(): array
    {
        $parser = (new ParserFactory())->create(ParserFactory::ONLY_PHP7);

        $className = UniqueIdentifierGenerator::getIdentifier('Foo');
        $classCode = 'class ' . $className . ' { private $bar; private $baz; protected $tab; '
            . 'protected $tar; public $taw; public $tam; }';

        eval($classCode);

        /** @var class-string $staticClassName */
        $staticClassName = UniqueIdentifierGenerator::getIdentifier('Foo');
        $staticClassCode = 'class ' . $staticClassName . ' { private static $bar; '
            . 'protected static $baz; public static $tab; private $taz; }';

        eval($staticClassCode);

        $parsedClassCode = $parser->parse('<?php ' . $classCode);
        $parsedStaticClassCode = $parser->parse('<?php ' . $staticClassCode);

        assert(class_exists($className, false));
        assert(class_exists($staticClassCode, false));
        assert(!empty($parsedClassCode));
        assert(!empty($parsedStaticClassCode));
        assert($parsedClassCode[0] instanceof Class_);
        assert($parsedStaticClassCode[0] instanceof Class_);

        return [
            [$className, $parsedClassCode[0], ['bar', 'baz', 'tab', 'tar', 'taw', 'tam']],
            [$staticClassName, $parsedStaticClassCode[0], ['taz']],
        ];
    }
}
