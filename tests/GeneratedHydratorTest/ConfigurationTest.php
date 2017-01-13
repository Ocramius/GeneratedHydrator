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

declare(strict_types=1);

namespace GeneratedHydratorTest;

use CodeGenerationUtils\Autoloader\AutoloaderInterface;
use CodeGenerationUtils\GeneratorStrategy\GeneratorStrategyInterface;
use CodeGenerationUtils\Inflector\ClassNameInflectorInterface;
use GeneratedHydrator\ClassGenerator\HydratorGeneratorInterface;
use PHPUnit_Framework_TestCase;
use GeneratedHydrator\Configuration;
use GeneratedHydrator\Factory\HydratorFactory;

/**
 * Tests for {@see \GeneratedHydrator\Configuration}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
class ConfigurationTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \GeneratedHydrator\Configuration
     */
    protected $configuration;

    /**
     * {@inheritDoc}
     *
     * @covers \GeneratedHydrator\Configuration::__construct
     */
    public function setUp()
    {
        $this->configuration = new Configuration('test');
    }

    /**
     * @covers \GeneratedHydrator\Configuration::setHydratedClassName
     * @covers \GeneratedHydrator\Configuration::getHydratedClassName
     */
    public function testGetSetHydratedClassName()
    {
        self::assertSame('test', $this->configuration->getHydratedClassName());
        $this->configuration->setHydratedClassName('bar');
        self::assertSame('bar', $this->configuration->getHydratedClassName());
    }

    /**
     * @covers \GeneratedHydrator\Configuration::createFactory
     */
    public function testCreateFactory()
    {
        self::assertInstanceOf(HydratorFactory::class, $this->configuration->createFactory());
    }

    /**
     * @covers \GeneratedHydrator\Configuration::doesAutoGenerateProxies
     * @covers \GeneratedHydrator\Configuration::setAutoGenerateProxies
     */
    public function testGetSetAutoGenerateProxies()
    {
        self::assertTrue($this->configuration->doesAutoGenerateProxies(), 'Default setting check for BC');

        $this->configuration->setAutoGenerateProxies(false);
        self::assertFalse($this->configuration->doesAutoGenerateProxies());

        $this->configuration->setAutoGenerateProxies(true);
        self::assertTrue($this->configuration->doesAutoGenerateProxies());
    }

    /**
     * @covers \GeneratedHydrator\Configuration::getGeneratedClassesNamespace
     * @covers \GeneratedHydrator\Configuration::setGeneratedClassesNamespace
     */
    public function testGetSetProxiesNamespace()
    {
        self::assertSame(
            'GeneratedHydratorGeneratedClass',
            $this->configuration->getGeneratedClassesNamespace(),
            'Default setting check for BC'
        );

        $this->configuration->setGeneratedClassesNamespace('foo');
        self::assertSame('foo', $this->configuration->getGeneratedClassesNamespace());
    }

    /**
     * @covers \GeneratedHydrator\Configuration::getClassNameInflector
     * @covers \GeneratedHydrator\Configuration::setClassNameInflector
     */
    public function testSetGetClassNameInflector()
    {
        self::assertInstanceOf(ClassNameInflectorInterface::class, $this->configuration->getClassNameInflector());

        /* @var $inflector ClassNameInflectorInterface|\PHPUnit_Framework_MockObject_MockObject */
        $inflector = $this->createMock(ClassNameInflectorInterface::class);

        $this->configuration->setClassNameInflector($inflector);
        self::assertSame($inflector, $this->configuration->getClassNameInflector());
    }

    /**
     * @covers \GeneratedHydrator\Configuration::getGeneratorStrategy
     * @covers \GeneratedHydrator\Configuration::setGeneratorStrategy
     */
    public function testSetGetGeneratorStrategy()
    {

        self::assertInstanceOf(GeneratorStrategyInterface::class, $this->configuration->getGeneratorStrategy());

        /* @var $strategy GeneratorStrategyInterface|\PHPUnit_Framework_MockObject_MockObject */
        $strategy = $this->createMock(GeneratorStrategyInterface::class);

        $this->configuration->setGeneratorStrategy($strategy);
        self::assertSame($strategy, $this->configuration->getGeneratorStrategy());
    }

    /**
     * @covers \GeneratedHydrator\Configuration::getGeneratedClassesTargetDir
     * @covers \GeneratedHydrator\Configuration::setGeneratedClassesTargetDir
     */
    public function testSetGetProxiesTargetDir()
    {
        self::assertTrue(is_dir($this->configuration->getGeneratedClassesTargetDir()));

        $this->configuration->setGeneratedClassesTargetDir(__DIR__);
        self::assertSame(__DIR__, $this->configuration->getGeneratedClassesTargetDir());
    }

    /**
     * @covers \GeneratedHydrator\Configuration::getGeneratedClassAutoloader
     * @covers \GeneratedHydrator\Configuration::setGeneratedClassAutoloader
     */
    public function testSetGetGeneratedClassAutoloader()
    {
        self::assertInstanceOf(AutoloaderInterface::class, $this->configuration->getGeneratedClassAutoloader());

        /* @var $autoloader AutoloaderInterface|\PHPUnit_Framework_MockObject_MockObject */
        $autoloader = $this->createMock(AutoloaderInterface::class);

        $this->configuration->setGeneratedClassAutoloader($autoloader);
        self::assertSame($autoloader, $this->configuration->getGeneratedClassAutoloader());
    }

    /**
     * @covers \GeneratedHydrator\Configuration::getHydratorGenerator
     * @covers \GeneratedHydrator\Configuration::setHydratorGenerator
     */
    public function testSetGetHydratorGenerator()
    {
        self::assertInstanceOf(HydratorGeneratorInterface::class, $this->configuration->getHydratorGenerator());

        /* @var $generator HydratorGeneratorInterface */
        $generator = $this->createMock(HydratorGeneratorInterface::class);

        $this->configuration->setHydratorGenerator($generator);
        self::assertSame($generator, $this->configuration->getHydratorGenerator());
    }
}
