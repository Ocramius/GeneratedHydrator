<?php

declare(strict_types=1);

namespace GeneratedHydratorTest\Functional;

use CodeGenerationUtils\GeneratorStrategy\EvaluatingGeneratorStrategy;
use CodeGenerationUtils\Inflector\ClassNameInflectorInterface;
use CodeGenerationUtils\Inflector\Util\UniqueIdentifierGenerator;
use GeneratedHydrator\Configuration;
use GeneratedHydratorTestAsset\BaseClass;
use GeneratedHydratorTestAsset\ClassWithMixedProperties;
use GeneratedHydratorTestAsset\ClassWithPrivateProperties;
use GeneratedHydratorTestAsset\ClassWithPrivatePropertiesAndParent;
use GeneratedHydratorTestAsset\ClassWithPrivatePropertiesAndParents;
use GeneratedHydratorTestAsset\ClassWithProtectedProperties;
use GeneratedHydratorTestAsset\ClassWithPublicProperties;
use GeneratedHydratorTestAsset\ClassWithStaticProperties;
use GeneratedHydratorTestAsset\ClassWithTypedProperties;
use GeneratedHydratorTestAsset\EmptyClass;
use GeneratedHydratorTestAsset\HydratedObject;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use stdClass;
use Zend\Hydrator\HydratorInterface;
use function get_class;
use function ksort;

/**
 * Tests for {@see \GeneratedHydrator\ClassGenerator\HydratorGenerator} produced objects
 *
 * @group Functional
 */
class HydratorFunctionalTest extends TestCase
{
    /**
     * @dataProvider getHydratorClasses
     */
    public function testHydrator(object $instance) : void
    {
        $reflection  = new ReflectionClass($instance);
        $initialData = [];
        $newData     = [];

        $this->recursiveFindInitialData($reflection, $instance, $initialData, $newData);

        $generatedClass = $this->generateHydrator($instance);

        // Hydration and extraction don't guarantee ordering.
        ksort($initialData);
        ksort($newData);
        $extracted = $generatedClass->extract($instance);
        ksort($extracted);

        self::assertSame($initialData, $extracted);
        self::assertSame($instance, $generatedClass->hydrate($newData, $instance));

        // Same as upper applies
        $inspectionData = [];
        $this->recursiveFindInspectionData($reflection, $instance, $inspectionData);
        ksort($inspectionData);
        $extracted = $generatedClass->extract($instance);
        ksort($extracted);

        self::assertSame($inspectionData, $newData);
        self::assertSame($inspectionData, $extracted);
    }

    public function testHydratingNull() : void
    {
        $instance = new ClassWithPrivateProperties();

        self::assertSame('property0', $instance->getProperty0());

        $this->generateHydrator($instance)->hydrate(['property0' => null], $instance);

        self::assertNull($instance->getProperty0());
    }

    /**
     * Ensures that the hydrator will not attempt to read unitialized PHP >= 7.4
     * typed property, which would cause "Uncaught Error: Typed property Foo::$a
     * must not be accessed before initialization" PHP engine errors.
     *
     * @requires PHP >= 7.4
     */
    public function testHydratorWillNotRaisedUnitiliazedTypedPropertyAccessError() : void
    {
        $instance = new ClassWithTypedProperties();
        $hydrator = $this->generateHydrator($instance);

        $hydrator->hydrate([
            // 'property0' has a default value, it should keep it.
            // 'property1' has a default value, it should keep it.
            'property2' => 3,
            // 'property3' is not required, it should remain null.
            // 'property4' default value is null, it should remain null.
        ], $instance);

        self::assertSame([
            'property0' => 1,
            'property1' => 2,
            'property2' => 3,
            'property3' => null,
            'property4' => null,
        ], $hydrator->extract($instance));
    }

    /**
     * @requires PHP >= 7.4
     */
    public function testHydratorWillSetAllTypedProperties() : void
    {
        $instance = new ClassWithTypedProperties();
        $hydrator = $this->generateHydrator($instance);

        $reference = [
            'property0' => 11,
            'property1' => null, // Ensure explicit set null works as expected.
            'property2' => 13,
            'property3' => 17,
            'property4' => 19,
        ];

        $hydrator->hydrate($reference, $instance);

        self::assertSame($reference, $hydrator->extract($instance));
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
     * Generates a hydrator for the given class name, and retrieves its class name
     */
    private function generateHydrator(object $instance) : HydratorInterface
    {
        $parentClassName    = get_class($instance);
        $generatedClassName = __NAMESPACE__ . '\\' . UniqueIdentifierGenerator::getIdentifier('Foo');
        $config             = new Configuration($parentClassName);
        /** @var ClassNameInflectorInterface|MockObject $inflector*/
        $inflector = $this->createMock(ClassNameInflectorInterface::class);

        $inflector
            ->expects(self::any())
            ->method('getGeneratedClassName')
            ->with($parentClassName)
            ->will(self::returnValue($generatedClassName));
        $inflector
            ->expects(self::any())
            ->method('getUserClassName')
            ->with($parentClassName)
            ->will(self::returnValue($parentClassName));

        $config->setClassNameInflector($inflector);
        $config->setGeneratorStrategy(new EvaluatingGeneratorStrategy());

        $generatedClass = $config->createFactory()->getHydratorClass();

        return new $generatedClass();
    }
}
