<?php

declare(strict_types=1);

namespace GeneratedHydratorTest\ClassGenerator;

use CodeGenerationUtils\GeneratorStrategy\EvaluatingGeneratorStrategy;
use CodeGenerationUtils\Inflector\Util\UniqueIdentifierGenerator;
use CodeGenerationUtils\Visitor\ClassRenamerVisitor;
use GeneratedHydrator\ClassGenerator\DefaultHydratorGenerator;
use GeneratedHydrator\GeneratedHydrator;
use GeneratedHydratorTestAsset\BaseClass;
use GeneratedHydratorTestAsset\ClassWithByRefMagicMethods;
use GeneratedHydratorTestAsset\ClassWithMagicMethods;
use GeneratedHydratorTestAsset\ClassWithMixedProperties;
use Laminas\Hydrator\HydratorInterface;
use PhpParser\NodeTraverser;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * Tests for {@see \GeneratedHydrator\ClassGenerator\HydratorGenerator}
 *
 * @covers \GeneratedHydrator\ClassGenerator\DefaultHydratorGenerator
 */
class HydratorGeneratorTest extends TestCase
{
    /**
     * @psalm-param class-string $className
     *
     * Verifies that generated code is valid and implements expected interfaces
     *
     * @dataProvider getTestedImplementations
     */
    public function testGeneratesValidCode(string $className): void
    {
        $generator = new DefaultHydratorGenerator();
        /** @psalm-var class-string $generatedClassName */
        $generatedClassName = UniqueIdentifierGenerator::getIdentifier('HydratorGeneratorTest');
        $originalClass      = new ReflectionClass($className);
        $generatorStrategy  = new EvaluatingGeneratorStrategy();
        $traverser          = new NodeTraverser();

        $traverser->addVisitor(new ClassRenamerVisitor($originalClass, $generatedClassName));
        $generatorStrategy->generate($traverser->traverse($generator->generate($originalClass)));

        $generatedReflection = new ReflectionClass($generatedClassName);

        self::assertSame($generatedClassName, $generatedReflection->getName());

        foreach ($this->getExpectedImplementedInterfaces() as $interface) {
            self::assertTrue($generatedReflection->implementsInterface($interface));
        }
    }

    /**
     * @return string[][]
     * @psalm-return non-empty-list<array{class-string}>
     */
    public function getTestedImplementations(): array
    {
        return [
            [BaseClass::class],
            [ClassWithMagicMethods::class],
            [ClassWithByRefMagicMethods::class],
            [ClassWithMixedProperties::class],
        ];
    }

    /** @psalm-return non-empty-list<class-string> */
    protected function getExpectedImplementedInterfaces(): array
    {
        return [
            GeneratedHydrator::class,
            HydratorInterface::class,
        ];
    }
}
