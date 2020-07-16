<?php

declare(strict_types=1);

namespace GeneratedHydratorTest\Functional;

use GeneratedHydrator\ClosureGenerator\Runtime\GenerateRuntimeExtractor;
use GeneratedHydrator\ClosureGenerator\Runtime\GenerateRuntimeHydrator;
use GeneratedHydratorTestAsset\BaseClass;
use GeneratedHydratorTestAsset\ClassWithMixedProperties;
use GeneratedHydratorTestAsset\ClassWithPrivateProperties;
use GeneratedHydratorTestAsset\ClassWithPrivatePropertiesAndParent;
use GeneratedHydratorTestAsset\ClassWithPrivatePropertiesAndParents;
use GeneratedHydratorTestAsset\ClassWithProtectedProperties;
use GeneratedHydratorTestAsset\ClassWithPublicProperties;
use GeneratedHydratorTestAsset\ClassWithStaticProperties;
use GeneratedHydratorTestAsset\EmptyClass;
use GeneratedHydratorTestAsset\HydratedObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionException;
use stdClass;
use function get_class;
use function ksort;

/**
 * Tests for {@see \GeneratedHydrator\ClassGenerator\HydratorGenerator} produced objects
 *
 * @group Functional
 */
class GenerateClosureRuntimeFunctionalTest extends TestCase
{
    /**
     * @throws ReflectionException
     *
     * @dataProvider getHydratorClasses
     */
    public function testHydrator(object $instance) : void
    {
        $reflection  = new ReflectionClass($instance);
        $initialData = [];
        $newData     = [];

        $this->recursiveFindInitialData($reflection, $instance, $initialData, $newData);

        $extract = $this->generateExtractor($instance);
        $hydrate = $this->generateHydrator($instance);

        // Hydration and extraction don't guarantee ordering.
        ksort($initialData);
        ksort($newData);
        $extracted = $extract($instance);
        ksort($extracted);

        self::assertSame($initialData, $extracted);
        self::assertSame($instance, $hydrate($newData, $instance));

        // Same as upper applies
        $inspectionData = [];
        $this->recursiveFindInspectionData($reflection, $instance, $inspectionData);
        ksort($inspectionData);
        $extracted = $extract($instance);
        ksort($extracted);

        self::assertSame($inspectionData, $newData);
        self::assertSame($inspectionData, $extracted);
    }

    /**
     * @throws ReflectionException
     */
    public function testHydratingNull() : void
    {
        $instance = new ClassWithPrivateProperties();

        self::assertSame('property0', $instance->getProperty0());

        ($this->generateHydrator($instance))(['property0' => null], $instance);

        self::assertNull($instance->getProperty0());
    }

    /**
     * @return mixed[]
     */
    public function getHydratorClasses() : array
    {
        return [
            [new stdClass()],
            [new EmptyClass()],
            [new HydratedObject()],
            [new BaseClass()],
            [new ClassWithPublicProperties()],
            [new ClassWithProtectedProperties()],
            [new ClassWithPrivateProperties()],
            [new ClassWithPrivatePropertiesAndParent()],
            [new ClassWithPrivatePropertiesAndParents()],
            [new ClassWithMixedProperties()],
            [new ClassWithStaticProperties()],
        ];
    }

    /**
     * Recursively populate the $initialData and $newData array browsing the
     * full class hierarchy tree
     *
     * Private properties from parent class that are hidden by children will be
     * dropped from the populated arrays
     *
     * @param mixed[] $initialData
     * @param mixed[] $newData
     */
    private function recursiveFindInitialData(
        ReflectionClass $class,
        object $instance,
        array &$initialData,
        array &$newData
    ) : void {
        $parentClass = $class->getParentClass();
        if ($parentClass) {
            $this->recursiveFindInitialData($parentClass, $instance, $initialData, $newData);
        }

        foreach ($class->getProperties() as $property) {
            if ($property->isStatic()) {
                continue;
            }

            $propertyName = $property->getName();

            $property->setAccessible(true);
            $initialData[$propertyName] = $property->getValue($instance);
            $newData[$propertyName]     = $property->getName() . '__new__value';
        }
    }

    /**
     * Recursively populate the $inspectedData array browsing the full class
     * hierarchy tree
     *
     * Private properties from parent class that are hidden by children will be
     * dropped from the populated arrays
     *
     * @param mixed[] $inspectionData
     */
    private function recursiveFindInspectionData(
        ReflectionClass $class,
        object $instance,
        array &$inspectionData
    ) : void {
        $parentClass = $class->getParentClass();
        if ($parentClass) {
            $this->recursiveFindInspectionData($parentClass, $instance, $inspectionData);
        }

        foreach ($class->getProperties() as $property) {
            if ($property->isStatic()) {
                continue;
            }

            $propertyName = $property->getName();

            $property->setAccessible(true);
            $inspectionData[$propertyName] = $property->getValue($instance);
        }
    }

    /**
     * @throws ReflectionException
     */
    private function generateExtractor(object $instance) : callable
    {
        $className         = get_class($instance);
        $generateExtractor = new GenerateRuntimeExtractor();

        return $generateExtractor($className);
    }

    /**
     * @throws ReflectionException
     */
    private function generateHydrator(object $instance) : callable
    {
        $className         = get_class($instance);
        $generateExtractor = new GenerateRuntimeHydrator();

        return $generateExtractor($className);
    }
}
