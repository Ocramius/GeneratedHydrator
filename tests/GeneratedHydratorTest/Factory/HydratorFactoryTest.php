<?php

declare(strict_types=1);

namespace GeneratedHydratorTest\Factory;

use CodeGenerationUtils\Autoloader\AutoloaderInterface;
use CodeGenerationUtils\GeneratorStrategy\GeneratorStrategyInterface;
use CodeGenerationUtils\Inflector\ClassNameInflectorInterface;
use CodeGenerationUtils\Inflector\Util\UniqueIdentifierGenerator;
use GeneratedHydrator\ClassGenerator\DefaultHydratorGenerator;
use GeneratedHydrator\Configuration;
use GeneratedHydrator\Factory\HydratorFactory;
use GeneratedHydratorTestAsset\BaseClass;
use GeneratedHydratorTestAsset\EmptyClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Tests for {@see \GeneratedHydrator\Factory\HydratorFactory}
 */
class HydratorFactoryTest extends TestCase
{
    /** @var ClassNameInflectorInterface&MockObject */
    protected ClassNameInflectorInterface $inflector;
    /** @var Configuration&MockObject */
    protected $config;

    public function setUp(): void
    {
        $this->inflector = $this->createMock(ClassNameInflectorInterface::class);
        $this->config    = $this->createMock(Configuration::class);

        $this->config->method('getClassNameInflector')
            ->willReturn($this->inflector);
    }

    /**
     * {@inheritDoc}
     *
     * @covers \GeneratedHydrator\Factory\HydratorFactory::__construct
     * @covers \GeneratedHydrator\Factory\HydratorFactory::getHydratorClass
     */
    public function testWillSkipAutoGeneration(): void
    {
        $className = UniqueIdentifierGenerator::getIdentifier('foo');

        $this->config->method('getHydratedClassName')->will(self::returnValue($className));
        $this->config->method('doesAutoGenerateProxies')->will(self::returnValue(false));
        $this
            ->inflector
            ->method('getUserClassName')
            ->with($className)
            ->will(self::returnValue('GeneratedHydratorTestAsset\BaseClass'));

        $this
            ->inflector
            ->expects(self::once())
            ->method('getGeneratedClassName')
            ->with(BaseClass::class)
            ->willReturn(EmptyClass::class);

        $factory        = new HydratorFactory($this->config);
        $generatedClass = $factory->getHydratorClass();

        self::assertInstanceOf(EmptyClass::class, new $generatedClass());
    }

    /**
     * {@inheritDoc}
     *
     * @covers \GeneratedHydrator\Factory\HydratorFactory::__construct
     * @covers \GeneratedHydrator\Factory\HydratorFactory::getHydratorClass
     *
     * NOTE: serious mocking going on in here (a class is generated on-the-fly) - careful
     */
    public function testWillTryAutoGeneration(): void
    {
        $className = UniqueIdentifierGenerator::getIdentifier('foo');
        /** @psalm-var class-string $generatedClassName */
        $generatedClassName = UniqueIdentifierGenerator::getIdentifier('bar');
        $generator          = $this->createMock(GeneratorStrategyInterface::class);
        $autoloader         = $this->createMock(AutoloaderInterface::class);

        $this->config->method('getHydratedClassName')->will(self::returnValue($className));
        $this->config->method('doesAutoGenerateProxies')->will(self::returnValue(true));
        $this->config->method('getGeneratorStrategy')->will(self::returnValue($generator));
        $this->config->method('getHydratorGenerator')->willReturn(new DefaultHydratorGenerator());
        $this
            ->config
            ->method('getGeneratedClassAutoloader')
            ->will(self::returnValue($autoloader));

        $generator
            ->expects(self::once())
            ->method('generate')
            ->with(self::isType('array'));

        // simulate autoloading
        $autoloader
            ->expects(self::once())
            ->method('__invoke')
            ->with($generatedClassName)
            ->willReturnCallback(static function () use ($generatedClassName): bool {
                eval('class ' . $generatedClassName . ' {}');

                return true;
            });

        $this
            ->inflector
            ->expects(self::once())
            ->method('getGeneratedClassName')
            ->with('GeneratedHydratorTestAsset\BaseClass')
            ->will(self::returnValue($generatedClassName));

        $this
            ->inflector
            ->expects(self::once())
            ->method('getUserClassName')
            ->with($className)
            ->will(self::returnValue('GeneratedHydratorTestAsset\BaseClass'));

        $factory        = new HydratorFactory($this->config);
        $generatedClass = $factory->getHydratorClass();

        self::assertInstanceOf($generatedClassName, new $generatedClass());
    }
}
