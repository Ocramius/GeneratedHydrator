<?php

declare(strict_types=1);

namespace GeneratedHydrator\Factory;

use CodeGenerationUtils\Visitor\ClassRenamerVisitor;
use GeneratedHydrator\Configuration;
use PhpParser\NodeTraverser;
use ReflectionClass;

/**
 * Factory responsible of producing hydrators
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
class HydratorFactory
{
    /**
     * @var \GeneratedHydrator\Configuration
     */
    private $configuration;

    /**
     * @param \GeneratedHydrator\Configuration $configuration
     */
    public function __construct(Configuration $configuration)
    {
        $this->configuration = clone $configuration;
    }

    /**
     * Retrieves the generated hydrator FQCN
     *
     * @return string
     *
     * @throws \CodeGenerationUtils\Exception\InvalidGeneratedClassesDirectoryException
     */
    public function getHydratorClass() : string
    {
        $inflector         = $this->configuration->getClassNameInflector();
        $realClassName     = $inflector->getUserClassName($this->configuration->getHydratedClassName());
        $hydratorClassName = $inflector->getGeneratedClassName($realClassName, array('factory' => get_class($this)));

        if (! class_exists($hydratorClassName) && $this->configuration->doesAutoGenerateProxies()) {
            $generator     = $this->configuration->getHydratorGenerator();
            $originalClass = new ReflectionClass($realClassName);
            $generatedAst   = $generator->generate($originalClass);
            $traverser      = new NodeTraverser();

            $traverser->addVisitor(new ClassRenamerVisitor($originalClass, $hydratorClassName));

            $this->configuration->getGeneratorStrategy()->generate($traverser->traverse($generatedAst));
            $this->configuration->getGeneratedClassAutoloader()->__invoke($hydratorClassName);
        }

        return $hydratorClassName;
    }
}
