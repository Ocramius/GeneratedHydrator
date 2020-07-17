<?php

declare(strict_types=1);

namespace GeneratedHydrator;

use CodeGenerationUtils\Autoloader\Autoloader;
use CodeGenerationUtils\Autoloader\AutoloaderInterface;
use CodeGenerationUtils\Exception\InvalidGeneratedClassesDirectoryException;
use CodeGenerationUtils\FileLocator\FileLocator;
use CodeGenerationUtils\GeneratorStrategy\FileWriterGeneratorStrategy;
use CodeGenerationUtils\GeneratorStrategy\GeneratorStrategyInterface;
use CodeGenerationUtils\Inflector\ClassNameInflector;
use CodeGenerationUtils\Inflector\ClassNameInflectorInterface;
use GeneratedHydrator\ClassGenerator\DefaultHydratorGenerator;
use GeneratedHydrator\ClassGenerator\HydratorGenerator;
use GeneratedHydrator\Factory\HydratorFactory;

use function sys_get_temp_dir;

/**
 * Base configuration class for the generated hydrator - serves as micro disposable DIC/facade
 *
 * @template HydratedClass of object
 */
class Configuration
{
    public const DEFAULT_GENERATED_CLASS_NAMESPACE = 'GeneratedHydratorGeneratedClass';

    /** @psalm-var class-string<HydratedClass> $hydratedClassName */
    protected string $hydratedClassName;

    protected bool $autoGenerateProxies = true;

    protected ?string $generatedClassesTargetDir = null;

    protected string $generatedClassesNamespace = self::DEFAULT_GENERATED_CLASS_NAMESPACE;

    protected ?GeneratorStrategyInterface $generatorStrategy = null;

    /** @var callable|null */
    protected $generatedClassesAutoloader;

    protected ?ClassNameInflectorInterface $classNameInflector = null;

    protected ?HydratorGenerator $hydratorGenerator = null;

    /** @psalm-param class-string<HydratedClass> $hydratedClassName */
    public function __construct(string $hydratedClassName)
    {
        $this->setHydratedClassName($hydratedClassName);
    }

    /** @psalm-return HydratorFactory<HydratedClass> */
    public function createFactory(): HydratorFactory
    {
        return new HydratorFactory($this);
    }

    /**
     * @psalm-param class-string<HydratedClass> $hydratedClassName
     *
     * @deprecated setting the hydrated class name **after** configuration is set is not really
     *             supported anymore, since it changes the underlying templated type of this
     *             object at runtime, which is not something we want. This method will be removed
     *             in a future release of this library.
     */
    public function setHydratedClassName(string $hydratedClassName): void
    {
        $this->hydratedClassName = $hydratedClassName;
    }

    /** @psalm-return class-string<HydratedClass> */
    public function getHydratedClassName(): string
    {
        return $this->hydratedClassName;
    }

    public function setAutoGenerateProxies(bool $autoGenerateProxies): void
    {
        $this->autoGenerateProxies = $autoGenerateProxies;
    }

    public function doesAutoGenerateProxies(): bool
    {
        return $this->autoGenerateProxies;
    }

    public function setGeneratedClassAutoloader(AutoloaderInterface $generatedClassesAutoloader): void
    {
        $this->generatedClassesAutoloader = $generatedClassesAutoloader;
    }

    /**
     * @throws InvalidGeneratedClassesDirectoryException
     */
    public function getGeneratedClassAutoloader(): AutoloaderInterface
    {
        if ($this->generatedClassesAutoloader === null) {
            $this->generatedClassesAutoloader = new Autoloader(
                new FileLocator($this->getGeneratedClassesTargetDir()),
                $this->getClassNameInflector()
            );
        }

        return $this->generatedClassesAutoloader;
    }

    public function setGeneratedClassesNamespace(string $generatedClassesNamespace): void
    {
        $this->generatedClassesNamespace = $generatedClassesNamespace;
    }

    public function getGeneratedClassesNamespace(): string
    {
        return $this->generatedClassesNamespace;
    }

    public function setGeneratedClassesTargetDir(string $generatedClassesTargetDir): void
    {
        $this->generatedClassesTargetDir = $generatedClassesTargetDir;
    }

    public function getGeneratedClassesTargetDir(): string
    {
        if ($this->generatedClassesTargetDir === null) {
            $this->generatedClassesTargetDir = sys_get_temp_dir();
        }

        return $this->generatedClassesTargetDir;
    }

    public function setGeneratorStrategy(GeneratorStrategyInterface $generatorStrategy): void
    {
        $this->generatorStrategy = $generatorStrategy;
    }

    /**
     * @throws InvalidGeneratedClassesDirectoryException
     */
    public function getGeneratorStrategy(): GeneratorStrategyInterface
    {
        if ($this->generatorStrategy === null) {
            $this->generatorStrategy = new FileWriterGeneratorStrategy(
                new FileLocator($this->getGeneratedClassesTargetDir())
            );
        }

        return $this->generatorStrategy;
    }

    public function setClassNameInflector(ClassNameInflectorInterface $classNameInflector): void
    {
        $this->classNameInflector = $classNameInflector;
    }

    public function getClassNameInflector(): ClassNameInflectorInterface
    {
        if ($this->classNameInflector === null) {
            $this->classNameInflector = new ClassNameInflector($this->getGeneratedClassesNamespace());
        }

        return $this->classNameInflector;
    }

    public function setHydratorGenerator(HydratorGenerator $hydratorGenerator): void
    {
        $this->hydratorGenerator = $hydratorGenerator;
    }

    public function getHydratorGenerator(): HydratorGenerator
    {
        if ($this->hydratorGenerator === null) {
            $this->hydratorGenerator = new DefaultHydratorGenerator();
        }

        return $this->hydratorGenerator;
    }
}
