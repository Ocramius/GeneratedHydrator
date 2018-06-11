<?php

declare(strict_types=1);

namespace GeneratedHydratorTest\ClassGenerator;

use CodeGenerationUtils\GeneratorStrategy\EvaluatingGeneratorStrategy;
use CodeGenerationUtils\Inflector\Util\UniqueIdentifierGenerator;
use CodeGenerationUtils\Visitor\ClassRenamerVisitor;
use GeneratedHydrator\ClassGenerator\DefaultHydratorGenerator;
use GeneratedHydratorTestAsset\BaseClass;
use GeneratedHydratorTestAsset\ClassWithByRefMagicMethods;
use GeneratedHydratorTestAsset\ClassWithMagicMethods;
use GeneratedHydratorTestAsset\ClassWithMixedProperties;
use PhpParser\NodeTraverser;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Zend\Hydrator\HydratorInterface;

/**
 * Tests for {@see \GeneratedHydrator\ClassGenerator\HydratorGenerator}
 *
 * @covers \GeneratedHydrator\ClassGenerator\DefaultHydratorGenerator
 */
class HydratorGeneratorTest extends TestCase
{
    /**
     * @dataProvider getTestedImplementations
     *
     * Verifies that generated code is valid and implements expected interfaces
     */
    public function testGeneratesValidCode(string $className) : void
    {
        $generator          = new DefaultHydratorGenerator();
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
     * @return string[]
     */
    public function getTestedImplementations() : array
    {
        return [
            [BaseClass::class],
            [ClassWithMagicMethods::class],
            [ClassWithByRefMagicMethods::class],
            [ClassWithMixedProperties::class],
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function getExpectedImplementedInterfaces() : array
    {
        return [HydratorInterface::class];
    }
}
