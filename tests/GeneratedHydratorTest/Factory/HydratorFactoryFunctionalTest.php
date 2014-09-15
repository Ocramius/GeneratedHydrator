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

use CodeGenerationUtils\GeneratorStrategy\EvaluatingGeneratorStrategy;
use CodeGenerationUtils\Inflector\Util\UniqueIdentifierGenerator;
use CodeGenerationUtils\ReflectionBuilder\ClassBuilder;
use CodeGenerationUtils\Visitor\ClassRenamerVisitor;
use GeneratedHydrator\Configuration;
use PhpParser\NodeTraverser;
use PHPUnit_Framework_TestCase;
use ReflectionClass;

/**
 * Integration tests for {@see \GeneratedHydrator\Factory\HydratorFactory}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 *
 * @group Functional
 */
class HydratorFactoryFunctionalTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \GeneratedHydrator\Configuration
     */
    protected $config;

    /**
     * @var string
     */
    protected $generatedClassName;

    /**
     * {@inheritDoc}
     */
    public function setUp()
    {
        $this->generatedClassName = UniqueIdentifierGenerator::getIdentifier('foo');
        $this->config             = new Configuration($this->generatedClassName);
        $generatorStrategy        = new EvaluatingGeneratorStrategy();
        $reflection               = new ReflectionClass('GeneratedHydratorTestAsset\\ClassWithMixedProperties');
        $generator                = new ClassBuilder();
        $traverser                = new NodeTraverser();
        $renamer                  = new ClassRenamerVisitor($reflection, $this->generatedClassName);

        $traverser->addVisitor($renamer);

        // evaluating the generated class
        //die(var_dump($traverser->traverse($generator->fromReflection($reflection))));
        $ast = $traverser->traverse($generator->fromReflection($reflection));
        $generatorStrategy->generate($ast);

        $this->config->setGeneratorStrategy($generatorStrategy);
    }

    /**
     * @covers \GeneratedHydrator\Factory\HydratorFactory::__construct
     * @covers \GeneratedHydrator\Factory\HydratorFactory::getHydratorClass
     */
    public function testWillGenerateValidClass()
    {
        $generatedClass = $this->config->createFactory()->getHydratorClass();

        $this->assertInstanceOf('Zend\\Stdlib\\Hydrator\\HydratorInterface', new $generatedClass);
    }
}
