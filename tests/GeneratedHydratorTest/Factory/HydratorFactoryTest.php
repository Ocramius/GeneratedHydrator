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

namespace GeneratedHydratorTest\Factory;

use CodeGenerationUtils\Autoloader\AutoloaderInterface;
use CodeGenerationUtils\GeneratorStrategy\GeneratorStrategyInterface;
use CodeGenerationUtils\Inflector\ClassNameInflectorInterface;
use CodeGenerationUtils\Inflector\Util\UniqueIdentifierGenerator;
use GeneratedHydrator\ClassGenerator\HydratorGenerator;
use GeneratedHydrator\Factory\HydratorFactory;
use PHPUnit_Framework_TestCase;
use GeneratedHydratorTestAsset\BaseClass;
use GeneratedHydratorTestAsset\EmptyClass;
use GeneratedHydrator\Configuration;

/**
 * Tests for {@see \GeneratedHydrator\Factory\HydratorFactory}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
class HydratorFactoryTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $inflector;

    /**
     * @var \GeneratedHydrator\Configuration|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $config;

    /**
     * {@inheritDoc}
     */
    public function setUp()
    {
        $this->inflector = $this->createMock(ClassNameInflectorInterface::class);
        $this->config    = $this
            ->getMockBuilder(Configuration::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this
            ->config
            ->expects(self::any())
            ->method('getClassNameInflector')
            ->will(self::returnValue($this->inflector));
    }

    /**
     * {@inheritDoc}
     *
     * @covers \GeneratedHydrator\Factory\HydratorFactory::__construct
     * @covers \GeneratedHydrator\Factory\HydratorFactory::getHydratorClass
     */
    public function testWillSkipAutoGeneration()
    {
        $className = UniqueIdentifierGenerator::getIdentifier('foo');

        $this->config->expects(self::any())->method('getHydratedClassName')->will(self::returnValue($className));
        $this->config->expects(self::any())->method('doesAutoGenerateProxies')->will(self::returnValue(false));
        $this
            ->inflector
            ->expects(self::any())
            ->method('getUserClassName')
            ->with($className)
            ->will(self::returnValue(BaseClass::class));

        $this
            ->inflector
            ->expects(self::once())
            ->method('getGeneratedClassName')
            ->with(BaseClass::class)
            ->will(self::returnValue(EmptyClass::class));

        $factory        = new HydratorFactory($this->config);
        $generatedClass = $factory->getHydratorClass();

        self::assertInstanceOf(EmptyClass::class, new $generatedClass);
    }

    /**
     * {@inheritDoc}
     *
     * @covers \GeneratedHydrator\Factory\HydratorFactory::__construct
     * @covers \GeneratedHydrator\Factory\HydratorFactory::getHydratorClass
     *
     * NOTE: serious mocking going on in here (a class is generated on-the-fly) - careful
     */
    public function testWillTryAutoGeneration()
    {
        $className          = UniqueIdentifierGenerator::getIdentifier('foo');
        $generatedClassName = UniqueIdentifierGenerator::getIdentifier('bar');
        $generator          = $this->createMock(GeneratorStrategyInterface::class);
        $autoloader         = $this->createMock(AutoloaderInterface::class);

        $this->config->expects(self::any())->method('getHydratedClassName')->will(self::returnValue($className));
        $this->config->expects(self::any())->method('doesAutoGenerateProxies')->will(self::returnValue(true));
        $this->config->expects(self::any())->method('getGeneratorStrategy')->will(self::returnValue($generator));
        $this->config->expects(self::any())->method('getHydratorGenerator')->willReturn(new HydratorGenerator());
        $this
            ->config
            ->expects(self::any())
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
            ->willReturnCallback(function () use ($generatedClassName) : bool {
                eval('class ' . $generatedClassName . ' {}');

                return true;
            });

        $this
            ->inflector
            ->expects(self::once())
            ->method('getGeneratedClassName')
            ->with(BaseClass::class)
            ->will(self::returnValue($generatedClassName));

        $this
            ->inflector
            ->expects(self::once())
            ->method('getUserClassName')
            ->with($className)
            ->will(self::returnValue(BaseClass::class));

        $factory        = new HydratorFactory($this->config);
        /* @var $generatedClass \GeneratedHydratorTestAsset\LazyLoadingMock */
        $generatedClass = $factory->getHydratorClass();

        self::assertInstanceOf($generatedClassName, new $generatedClass);
    }
}
