<?php

declare(strict_types=1);

namespace GeneratedHydrator\Factory;

use CodeGenerationUtils\Exception\InvalidGeneratedClassesDirectoryException;
use CodeGenerationUtils\Visitor\ClassRenamerVisitor;
use GeneratedHydrator\Configuration;
use GeneratedHydrator\GeneratedHydrator;
use Laminas\Hydrator\HydratorInterface;
use PhpParser\NodeTraverser;
use ReflectionClass;

use function assert;
use function class_exists;

/**
 * Factory responsible of producing hydrators
 *
 * @psalm-template HydratedObject of object
 */
class HydratorFactory
{
    private Configuration $configuration;

    /** @psalm-param Configuration<HydratedObject> $configuration */
    public function __construct(Configuration $configuration)
    {
        $this->configuration = clone $configuration;
    }

    /**
     * Retrieves the generated hydrator FQCN
     *
     * @throws InvalidGeneratedClassesDirectoryException
     *
     * @psalm-return class-string<GeneratedHydrator<HydratedObject>>
     */
    public function getHydratorClass(): string
    {
        $inflector         = $this->configuration->getClassNameInflector();
        /** @psalm-var class-string $realClassName */
        $realClassName     = $inflector->getUserClassName($this->configuration->getHydratedClassName());
        /** @psalm-var class-string<GeneratedHydrator<HydratedObject>> $hydratorClassName */
        $hydratorClassName = $inflector->getGeneratedClassName($realClassName, ['factory' => static::class]);

        if (! class_exists($hydratorClassName) && $this->configuration->doesAutoGenerateProxies()) {
            $generator     = $this->configuration->getHydratorGenerator();
            $originalClass = new ReflectionClass($realClassName);
            $generatedAst  = $generator->generate($originalClass);
            $traverser     = new NodeTraverser();

            $traverser->addVisitor(new ClassRenamerVisitor($originalClass, $hydratorClassName));

            $this->configuration->getGeneratorStrategy()->generate($traverser->traverse($generatedAst));
            $this->configuration->getGeneratedClassAutoloader()->__invoke($hydratorClassName);
        }

        return $hydratorClassName;
    }

    /**
     * Instantiates the generated hydrator class
     *
     * @throws InvalidGeneratedClassesDirectoryException
     *
     * @psalm-return GeneratedHydrator<HydratedObject>
     */
    public function getHydrator(): GeneratedHydrator
    {
        $hydratorClass = $this->getHydratorClass();

        return new $hydratorClass();
    }
}
