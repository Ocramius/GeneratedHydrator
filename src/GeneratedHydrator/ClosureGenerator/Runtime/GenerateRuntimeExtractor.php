<?php

declare(strict_types=1);

namespace GeneratedHydrator\ClosureGenerator\Runtime;

use Closure;
use GeneratedHydrator\ClosureGenerator\GenerateExtractor;
use GeneratedHydrator\Configuration;
use ReflectionClass;
use ReflectionException;
use stdClass;
use Zend\Hydrator\ExtractionInterface;

final class GenerateRuntimeExtractor implements GenerateExtractor
{
    /** @var callable[] */
    private static $extractorsCache;

    /** @var ReflectionClass[] */
    private static $classesCache;

    /**
     * @return callable function ($object) : array
     *
     * @throws ReflectionException
     */
    public function __invoke(string $className) : callable
    {
        if (isset(self::$extractorsCache[$className])) {
            return self::$extractorsCache[$className];
        }

        if ($className === stdClass::class) {
            return $this->generateWorkaroundExtractor($className);
        }

        $propertyNames   = $this->getPropertyNames($className);
        $parentExtractor = $this->generateParentExtractor($className);
        // Kind of micro optimization: avoid to inherit $parentHydrator from this scope and to check it in the closure
        if ($parentExtractor === null) {
            $extractor = static function (object $object) use ($propertyNames) : array {
                $data = [];
                foreach ($propertyNames as $propertyName) {
                    $data[$propertyName] = $object->{$propertyName};
                }

                return $data;
            };
        } else {
            $extractor = static function (object $object) use ($propertyNames, $parentExtractor) : array {
                $data = $parentExtractor($object);
                foreach ($propertyNames as $propertyName) {
                    $data[$propertyName] = $object->{$propertyName};
                }

                return $data;
            };
        }

        return self::$extractorsCache[$className] = Closure::bind($extractor, null, $className);
    }

    /**
     * @throws ReflectionException
     */
    private function generateParentExtractor(string $className) : ?callable
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
        /** @var ExtractionInterface $hydrator */
        $hydrator = new $generatedClass();

        return static function (object $object) use ($hydrator) {
            return $hydrator->extract($object);
        };
    }
}
