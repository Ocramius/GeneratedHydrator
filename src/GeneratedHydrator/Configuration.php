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
use GeneratedHydrator\ClassGenerator\HydratorGenerator;
use GeneratedHydrator\ClassGenerator\HydratorGeneratorInterface;
use GeneratedHydrator\Factory\HydratorFactory;
use function sys_get_temp_dir;

/**
 * Base configuration class for the generated hydrator - serves as micro disposable DIC/facade
 */
class Configuration
{
    const DEFAULT_GENERATED_CLASS_NAMESPACE = 'GeneratedHydratorGeneratedClass';

    /** @var string */
    protected $hydratedClassName;

    /** @var bool */
    protected $autoGenerateProxies = true;

    /** @var string|null */
    protected $generatedClassesTargetDir;

    /** @var string */
    protected $generatedClassesNamespace = self::DEFAULT_GENERATED_CLASS_NAMESPACE;

    /** @var GeneratorStrategyInterface|null */
    protected $generatorStrategy;

    /** @var callable|null */
    protected $generatedClassesAutoloader;

    /** @var ClassNameInflectorInterface|null */
    protected $classNameInflector;

    /** @var HydratorGeneratorInterface|null */
    protected $hydratorGenerator;

    public function __construct(string $hydratedClassName)
    {
        $this->setHydratedClassName($hydratedClassName);
    }

    public function createFactory() : HydratorFactory
    {
        return new HydratorFactory($this);
    }

    public function setHydratedClassName(string $hydratedClassName)
    {
        $this->hydratedClassName = $hydratedClassName;
    }

    public function getHydratedClassName() : string
    {
        return $this->hydratedClassName;
    }

    public function setAutoGenerateProxies(bool $autoGenerateProxies)
    {
        $this->autoGenerateProxies = $autoGenerateProxies;
    }

    public function doesAutoGenerateProxies() : bool
    {
        return $this->autoGenerateProxies;
    }

    public function setGeneratedClassAutoloader(AutoloaderInterface $generatedClassesAutoloader)
    {
        $this->generatedClassesAutoloader = $generatedClassesAutoloader;
    }

    /**
     * @throws InvalidGeneratedClassesDirectoryException
     */
    public function getGeneratedClassAutoloader() : AutoloaderInterface
    {
        if (null === $this->generatedClassesAutoloader) {
            $this->generatedClassesAutoloader = new Autoloader(
                new FileLocator($this->getGeneratedClassesTargetDir()),
                $this->getClassNameInflector()
            );
        }

        return $this->generatedClassesAutoloader;
    }

    public function setGeneratedClassesNamespace(string $generatedClassesNamespace)
    {
        $this->generatedClassesNamespace = $generatedClassesNamespace;
    }

    public function getGeneratedClassesNamespace() : string
    {
        return $this->generatedClassesNamespace;
    }

    public function setGeneratedClassesTargetDir(string $generatedClassesTargetDir)
    {
        $this->generatedClassesTargetDir = $generatedClassesTargetDir;
    }

    /**
     * @return null|string
     */
    public function getGeneratedClassesTargetDir()
    {
        if (null === $this->generatedClassesTargetDir) {
            $this->generatedClassesTargetDir = sys_get_temp_dir();
        }

        return $this->generatedClassesTargetDir;
    }

    public function setGeneratorStrategy(GeneratorStrategyInterface $generatorStrategy)
    {
        $this->generatorStrategy = $generatorStrategy;
    }

    /**
     * @return GeneratorStrategyInterface
     *
     * @throws InvalidGeneratedClassesDirectoryException
     */
    public function getGeneratorStrategy() : GeneratorStrategyInterface
    {
        if (null === $this->generatorStrategy) {
            $this->generatorStrategy = new FileWriterGeneratorStrategy(
                new FileLocator($this->getGeneratedClassesTargetDir())
            );
        }

        return $this->generatorStrategy;
    }

    public function setClassNameInflector(ClassNameInflectorInterface $classNameInflector)
    {
        $this->classNameInflector = $classNameInflector;
    }

    public function getClassNameInflector() : ClassNameInflectorInterface
    {
        if (null === $this->classNameInflector) {
            $this->classNameInflector = new ClassNameInflector($this->getGeneratedClassesNamespace());
        }

        return $this->classNameInflector;
    }

    public function setHydratorGenerator(HydratorGeneratorInterface $hydratorGenerator)
    {
        $this->hydratorGenerator = $hydratorGenerator;
    }

    /**
     * @return HydratorGeneratorInterface
     */
    public function getHydratorGenerator()
    {
        if (null === $this->hydratorGenerator) {
            $this->hydratorGenerator = new HydratorGenerator();
        }

        return $this->hydratorGenerator;
    }
}
