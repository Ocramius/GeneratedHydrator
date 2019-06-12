<?php

declare(strict_types=1);

namespace GeneratedHydratorTest\CodeGenerator\Visitor;

use CodeGenerationUtils\Inflector\Util\UniqueIdentifierGenerator;
use GeneratedHydrator\CodeGenerator\Visitor\HydratorMethodsVisitor;
use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\NodeFinder;
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
     * @param string[] $properties
     *
     * @dataProvider classAstProvider
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
                static function (Node $node) use ($methodName) : bool {
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

	/**
	 * @dataProvider propertyAstProvider
	 */
	public function testPropertyCodeGeneration(string $className, Class_ $classNode, array $properties) : void
	{
		$visitor = new HydratorMethodsVisitor(new ReflectionClass($className));

		/** @var Class_ $modifiedAst */
		$modifiedNode = $visitor->leaveNode($classNode);

		$constructors = $this->findConstructor($modifiedNode);
		self::assertCount(1, $constructors);
		$constructor = reset($constructors);

		$hydratePropertyNames = $this->findAssignedPropertyNames(
			$constructor,
			'hydrateCallbacks',
			'object',
			function (Assign $assign) {
				return $assign->var->name->name;
			}
		);
		$extractPropertyNames = $this->findAssignedPropertyNames(
			$constructor,
			'extractCallbacks',
			'values',
			function (Assign $assign) {
				return $assign->var->dim->value;
			}
		);

		self::assertSameSize($properties, $hydratePropertyNames);
		self::assertSameSize($properties, $extractPropertyNames);
		self::assertCount(0, array_diff($properties, $hydratePropertyNames));
		self::assertCount(0, array_diff($properties, $extractPropertyNames));
	}

	private function findConstructor(Class_ $class) : array {
		return array_filter(
			$class->stmts,
			static function (Node $node): bool {
				return $node instanceof ClassMethod && '__construct' === $node->name->name;
			}
		);
	}

	private function findAssignedPropertyNames(Node $node, string $callbackName, string $variableName, callable $mapper)
	{
		$finder = new NodeFinder();
		$callbacks = $finder->find($node, function(Node $node) use ($callbackName) {
			return $node instanceof Assign
				&& $node->var->var->name instanceof Node\Identifier
				&& $node->var->var->name->name === $callbackName;
		});

		$found = [];
		foreach($callbacks as $callback) {
			/** @noinspection SlowArrayOperationsInLoopInspection */
			$found = array_merge($found, $finder->find($callback, function(Node $node) use ($variableName) {
				return $node instanceof Assign
					&& is_string($node->var->var->name)
					&& $node->var->var->name === $variableName;
			}));
		}

		return array_map($mapper, $found);
	}

	/**
	 * @return Node[]
	 */
	public function propertyAstProvider(): array
	{
		$parser = (new ParserFactory())->create(ParserFactory::ONLY_PHP7);

		$className = UniqueIdentifierGenerator::getIdentifier('Foo');
		$classCode = 'class '.$className.' { private $bar; private $baz; protected $tab; '
			.'protected $tar; }';

		eval($classCode);

		$subClassName = UniqueIdentifierGenerator::getIdentifier('Foo');
		$subClassCode = 'class '.$subClassName.' extends '.$className.' { private $fuz; protected $buz; }';

		eval($subClassCode);

		$sub2ClassName = UniqueIdentifierGenerator::getIdentifier('Foo');
		$sub2ClassCode = 'class '.$sub2ClassName.' extends '.$subClassName.' { protected $bis; }';

		eval($sub2ClassCode);

		return [
			[$className, $parser->parse('<?php '.$classCode)[0], ['bar', 'baz', 'tab', 'tar']],
			[$subClassName, $parser->parse('<?php '.$subClassCode)[0], ['bar', 'baz', 'tab', 'tar', 'fuz', 'buz']],
			[
				$sub2ClassName,
				$parser->parse('<?php '.$sub2ClassCode)[0],
				['bar', 'baz', 'tab', 'tar', 'fuz', 'buz', 'bis'],
			],
		];
	}
}
