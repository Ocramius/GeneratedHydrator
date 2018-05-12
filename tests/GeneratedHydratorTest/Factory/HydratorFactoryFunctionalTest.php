<?php

declare(strict_types=1);

namespace GeneratedHydratorTest\Factory;

use CodeGenerationUtils\GeneratorStrategy\EvaluatingGeneratorStrategy;
use CodeGenerationUtils\Inflector\Util\UniqueIdentifierGenerator;
use CodeGenerationUtils\ReflectionBuilder\ClassBuilder;
use CodeGenerationUtils\Visitor\ClassRenamerVisitor;
use GeneratedHydrator\Configuration;
use PhpParser\NodeTraverser;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * Integration tests for {@see \GeneratedHydrator\Factory\HydratorFactory}
 *
 * @group Functional
 */
class HydratorFactoryFunctionalTest extends TestCase
{
    /** @var Configuration */
    protected $config;

    /** @var string */
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
    public function testWillGenerateValidClass() : void
    {
        $generatedClass = $this->config->createFactory()->getHydratorClass();

        self::assertInstanceOf('Zend\\Hydrator\\HydratorInterface', new $generatedClass());
    }
}
