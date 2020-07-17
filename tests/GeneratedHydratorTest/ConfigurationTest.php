<?php

declare(strict_types=1);

namespace GeneratedHydratorTest;

use CodeGenerationUtils\Autoloader\AutoloaderInterface;
use CodeGenerationUtils\GeneratorStrategy\GeneratorStrategyInterface;
use CodeGenerationUtils\Inflector\ClassNameInflectorInterface;
use GeneratedHydrator\ClassGenerator\HydratorGenerator;
use GeneratedHydrator\Configuration;
use GeneratedHydrator\Factory\HydratorFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

use stdClass;
use function assert;
use function is_dir;

/**
 * Tests for {@see \GeneratedHydrator\Configuration}
 *
 * @covers \GeneratedHydrator\Configuration
 */
class ConfigurationTest extends TestCase
{
    protected Configuration $configuration;

    public function setUp(): void
    {
        $this->configuration = new Configuration(stdClass::class);
    }

    public function testGetSetHydratedClassName(): void
    {
        self::assertSame(stdClass::class, $this->configuration->getHydratedClassName());
        $this->configuration->setHydratedClassName(__CLASS__);
        self::assertSame(__CLASS__, $this->configuration->getHydratedClassName());
    }

    public function testCreateFactory(): void
    {
        self::assertInstanceOf(HydratorFactory::class, $this->configuration->createFactory());
    }

    public function testGetSetAutoGenerateProxies(): void
    {
        self::assertTrue($this->configuration->doesAutoGenerateProxies(), 'Default setting check for BC');

        $this->configuration->setAutoGenerateProxies(false);
        self::assertFalse($this->configuration->doesAutoGenerateProxies());

        $this->configuration->setAutoGenerateProxies(true);
        self::assertTrue($this->configuration->doesAutoGenerateProxies());
    }

    public function testGetSetProxiesNamespace(): void
    {
        self::assertSame(
            'GeneratedHydratorGeneratedClass',
            $this->configuration->getGeneratedClassesNamespace(),
            'Default setting check for BC'
        );

        $this->configuration->setGeneratedClassesNamespace('foo');
        self::assertSame('foo', $this->configuration->getGeneratedClassesNamespace());
    }

    public function testSetGetClassNameInflector(): void
    {
        self::assertInstanceOf(ClassNameInflectorInterface::class, $this->configuration->getClassNameInflector());

        $inflector = $this->createMock(ClassNameInflectorInterface::class);

        $this->configuration->setClassNameInflector($inflector);
        self::assertSame($inflector, $this->configuration->getClassNameInflector());
    }

    public function testSetGetGeneratorStrategy(): void
    {
        self::assertInstanceOf(GeneratorStrategyInterface::class, $this->configuration->getGeneratorStrategy());

        $strategy = $this->createMock(GeneratorStrategyInterface::class);

        $this->configuration->setGeneratorStrategy($strategy);
        self::assertSame($strategy, $this->configuration->getGeneratorStrategy());
    }

    public function testSetGetProxiesTargetDir(): void
    {
        self::assertTrue(is_dir($this->configuration->getGeneratedClassesTargetDir()));

        $this->configuration->setGeneratedClassesTargetDir(__DIR__);
        self::assertSame(__DIR__, $this->configuration->getGeneratedClassesTargetDir());
    }

    public function testSetGetGeneratedClassAutoloader(): void
    {
        self::assertInstanceOf(AutoloaderInterface::class, $this->configuration->getGeneratedClassAutoloader());

        $autoloader = $this->createMock(AutoloaderInterface::class);

        $this->configuration->setGeneratedClassAutoloader($autoloader);
        self::assertSame($autoloader, $this->configuration->getGeneratedClassAutoloader());
    }

    public function testSetGetHydratorGenerator(): void
    {
        self::assertInstanceOf(HydratorGenerator::class, $this->configuration->getHydratorGenerator());

        $generator = $this->createMock(HydratorGenerator::class);

        $this->configuration->setHydratorGenerator($generator);
        self::assertSame($generator, $this->configuration->getHydratorGenerator());
    }
}
