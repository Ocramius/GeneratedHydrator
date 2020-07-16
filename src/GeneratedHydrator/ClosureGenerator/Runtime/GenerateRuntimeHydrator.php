<?php

declare(strict_types=1);

namespace GeneratedHydrator\ClosureGenerator\Runtime;

use Closure;
use GeneratedHydrator\ClosureGenerator\GenerateHydrator;
use GeneratedHydrator\Configuration;
use ReflectionClass;
use ReflectionException;
use RuntimeException;
use stdClass;
use Zend\Hydrator\HydratorInterface;
use function array_key_exists;

final class GenerateRuntimeHydrator implements GenerateHydrator
{
    /** @var callable[] */
    private static $hydratorsCache;

    /** @var ReflectionClass[] */
    private static $classesCache;

    /**
     * @throws ReflectionException
     */
    public function __invoke(string $className) : callable
    {
        if (isset(self::$hydratorsCache[$className])) {
            return self::$hydratorsCache[$className];
        }

        if ($className === stdClass::class) {
            return $this->generateWorkaroundExtractor($className);
        }

        $propertyNames = $this->getPropertyNames($className);

        $parentHydrator = $this->generateParentHydrator($className);
        // Kind of micro optimization :
        // in case of class without a parent avoid to inherit $parentHydrator from this scope and to check it in the closure
        if ($parentHydrator === null) {
            $extractor = static function ($data, $object) use ($propertyNames) {
                foreach ($propertyNames as $propertyName) {
                    if (! array_key_exists($propertyName, $data)) {
                        continue;
                    }
                    $object->{$propertyName} = $data[$propertyName];
                }

                return $object;
            };
        } else {
            $extractor = static function (
                $data,
                $object
            ) use (
                $propertyNames,
                $parentHydrator
            ) {
                $parentHydrator($data, $object);
                foreach ($propertyNames as $propertyName) {
                    if (! array_key_exists($propertyName, $data)) {
                        continue;
                    }
                    $object->{$propertyName} = $data[$propertyName];
                }

                return $object;
            };
        }

        return self::$hydratorsCache[$className] = Closure::bind($extractor, null, $className);
    }

    /**
     * @throws ReflectionException
     */
    private function generateParentHydrator(string $className) : ?callable
    {
        $parent = $this->getClass($className)->getParentClass();
        if ($parent === false) {
            return null;
        }

        return $this($parent->getName());
    }

    /**
     * @return string[]
     *
     * @throws ReflectionException
     */
    private function getPropertyNames(string $className) : array
    {
        $class         = $this->getClass($className);
        $propertyNames = [];
        foreach ($class->getProperties() as $property) {
            if ($property->isStatic()) {
                continue;
            }
            $propertyNames[] = $property->getName();
        }

        return $propertyNames;
    }

    /**
     * @throws ReflectionException
     * @throws RuntimeException
     */
    private function getClass(string $className) : ReflectionClass
    {
        if (isset(self::$classesCache[$className])) {
            return self::$classesCache[$className];
        }

        $class = new ReflectionClass($className);

        return self::$classesCache[$className] = $class;
    }

    private function generateWorkaroundExtractor(string $className) : callable
    {
        $config = new Configuration($className);

        $generatedClass = $config->createFactory()->getHydratorClass();
        /** @var HydratorInterface $hydrator */
        $hydrator = new $generatedClass();

        return static function (array $data, object $object) use ($hydrator) {
            return $hydrator->hydrate($data, $object);
        };
    }
}
