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

namespace GeneratedHydratorTest\Factory;

use CodeGenerationUtils\Inflector\Util\UniqueIdentifierGenerator;
use GeneratedHydrator\Factory\HydratorFactory;
use PHPUnit_Framework_TestCase;

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
        $this->inflector = $this->getMock('CodeGenerationUtils\\Inflector\\ClassNameInflectorInterface');
        $this->config    = $this
            ->getMockBuilder('GeneratedHydrator\\Configuration')
            ->disableOriginalConstructor()
            ->getMock();

        $this
            ->config
            ->expects($this->any())
            ->method('getClassNameInflector')
            ->will($this->returnValue($this->inflector));
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

        $this->config->expects($this->any())->method('getHydratedClassName')->will($this->returnValue($className));
        $this->config->expects($this->any())->method('doesAutoGenerateProxies')->will($this->returnValue(false));
        $this
            ->inflector
            ->expects($this->any())
            ->method('getUserClassName')
            ->with($className)
            ->will($this->returnValue('GeneratedHydratorTestAsset\\BaseClass'));

        $this
            ->inflector
            ->expects($this->once())
            ->method('getGeneratedClassName')
            ->with('GeneratedHydratorTestAsset\\BaseClass')
            ->will($this->returnValue('GeneratedHydratorTestAsset\\EmptyClass'));

        $factory        = new HydratorFactory($this->config);
        $generatedClass = $factory->getHydratorClass();

        $this->assertInstanceOf('GeneratedHydratorTestAsset\\EmptyClass', new $generatedClass);
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
        $className      = UniqueIdentifierGenerator::getIdentifier('foo');
        $generatedClassName = UniqueIdentifierGenerator::getIdentifier('bar');
        $generator      = $this->getMock('CodeGenerationUtils\\GeneratorStrategy\\GeneratorStrategyInterface');
        $autoloader     = $this->getMock('CodeGenerationUtils\\Autoloader\\AutoloaderInterface');

        $this->config->expects($this->any())->method('getHydratedClassName')->will($this->returnValue($className));
        $this->config->expects($this->any())->method('doesAutoGenerateProxies')->will($this->returnValue(true));
        $this->config->expects($this->any())->method('getGeneratorStrategy')->will($this->returnValue($generator));
        $this->config->expects($this->any())->method('getCustomVisitors')->will($this->returnValue([]));
        $this
            ->config
            ->expects($this->any())
            ->method('getGeneratedClassAutoloader')
            ->will($this->returnValue($autoloader));

        $generator
            ->expects($this->once())
            ->method('generate')
            ->with($this->isType('array'));

        // simulate autoloading
        $autoloader
            ->expects($this->once())
            ->method('__invoke')
            ->with($generatedClassName)
            ->will(
                $this->returnCallback(
                    function () use ($generatedClassName) {
                        eval('class ' . $generatedClassName . ' {}');
                    }
                )
            );

        $this
            ->inflector
            ->expects($this->once())
            ->method('getGeneratedClassName')
            ->with('GeneratedHydratorTestAsset\\BaseClass')
            ->will($this->returnValue($generatedClassName));

        $this
            ->inflector
            ->expects($this->once())
            ->method('getUserClassName')
            ->with($className)
            ->will($this->returnValue('GeneratedHydratorTestAsset\\BaseClass'));

        $factory        = new HydratorFactory($this->config);
        /* @var $generatedClass \GeneratedHydratorTestAsset\LazyLoadingMock */
        $generatedClass = $factory->getHydratorClass();

        $this->assertInstanceOf($generatedClassName, new $generatedClass);
    }
}
