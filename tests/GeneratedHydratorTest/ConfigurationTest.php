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

namespace GeneratedHydratorTest;

use PHPUnit_Framework_TestCase;
use GeneratedHydrator\Configuration;

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
        $this->assertSame('test', $this->configuration->getHydratedClassName());
        $this->configuration->setHydratedClassName('bar');
        $this->assertSame('bar', $this->configuration->getHydratedClassName());
    }

    /**
     * @covers \GeneratedHydrator\Configuration::createFactory
     */
    public function testCreateFactory()
    {
        $this->assertInstanceOf('GeneratedHydrator\\Factory\\HydratorFactory', $this->configuration->createFactory());
    }

    /**
     * @covers \GeneratedHydrator\Configuration::doesAutoGenerateProxies
     * @covers \GeneratedHydrator\Configuration::setAutoGenerateProxies
     */
    public function testGetSetAutoGenerateProxies()
    {
        $this->assertTrue($this->configuration->doesAutoGenerateProxies(), 'Default setting check for BC');

        $this->configuration->setAutoGenerateProxies(false);
        $this->assertFalse($this->configuration->doesAutoGenerateProxies());

        $this->configuration->setAutoGenerateProxies(true);
        $this->assertTrue($this->configuration->doesAutoGenerateProxies());
    }

    /**
     * @covers \GeneratedHydrator\Configuration::getGeneratedClassesNamespace
     * @covers \GeneratedHydrator\Configuration::setGeneratedClassesNamespace
     */
    public function testGetSetProxiesNamespace()
    {
        $this->assertSame(
            'GeneratedHydratorGeneratedProxy',
            $this->configuration->getGeneratedClassesNamespace(),
            'Default setting check for BC'
        );

        $this->configuration->setGeneratedClassesNamespace('foo');
        $this->assertSame('foo', $this->configuration->getGeneratedClassesNamespace());
    }

    /**
     * @covers \GeneratedHydrator\Configuration::getClassNameInflector
     * @covers \GeneratedHydrator\Configuration::setClassNameInflector
     */
    public function testSetGetClassNameInflector()
    {
        $this->assertInstanceOf(
            'CodeGenerationUtils\\Inflector\\ClassNameInflectorInterface',
            $this->configuration->getClassNameInflector()
        );

        $inflector = $this->getMock('CodeGenerationUtils\\Inflector\\ClassNameInflectorInterface');

        $this->configuration->setClassNameInflector($inflector);
        $this->assertSame($inflector, $this->configuration->getClassNameInflector());
    }

    /**
     * @covers \GeneratedHydrator\Configuration::getGeneratorStrategy
     * @covers \GeneratedHydrator\Configuration::setGeneratorStrategy
     */
    public function testSetGetGeneratorStrategy()
    {

        $this->assertInstanceOf(
            'CodeGenerationUtils\\GeneratorStrategy\\GeneratorStrategyInterface',
            $this->configuration->getGeneratorStrategy()
        );

        $strategy = $this->getMock('CodeGenerationUtils\\GeneratorStrategy\\GeneratorStrategyInterface');

        $this->configuration->setGeneratorStrategy($strategy);
        $this->assertSame($strategy, $this->configuration->getGeneratorStrategy());
    }

    /**
     * @covers \GeneratedHydrator\Configuration::getProxiesTargetDir
     * @covers \GeneratedHydrator\Configuration::setProxiesTargetDir
     */
    public function testSetGetProxiesTargetDir()
    {
        $this->assertTrue(is_dir($this->configuration->getProxiesTargetDir()));

        $this->configuration->setProxiesTargetDir(__DIR__);
        $this->assertSame(__DIR__, $this->configuration->getProxiesTargetDir());
    }

    /**
     * @covers \GeneratedHydrator\Configuration::getGeneratedClassAutoloader
     * @covers \GeneratedHydrator\Configuration::setGeneratedClassAutoloader
     */
    public function testSetGetProxyAutoloader()
    {
        $this->assertInstanceOf(
            'CodeGenerationUtils\\Autoloader\\AutoloaderInterface',
            $this->configuration->getGeneratedClassAutoloader()
        );

        $autoloader = $this->getMock('CodeGenerationUtils\\Autoloader\\AutoloaderInterface');

        $this->configuration->setGeneratedClassAutoloader($autoloader);
        $this->assertSame($autoloader, $this->configuration->getGeneratedClassAutoloader());
    }
}
