<?php

declare(strict_types=1);

namespace GeneratedHydratorTest\Functional;

use CodeGenerationUtils\GeneratorStrategy\EvaluatingGeneratorStrategy;
use CodeGenerationUtils\Inflector\ClassNameInflectorInterface;
use CodeGenerationUtils\Inflector\Util\UniqueIdentifierGenerator;
use GeneratedHydrator\ClassGenerator\AbstractHydratorGenerator;
use GeneratedHydrator\Configuration;
use GeneratedHydrator\Strategy\RecursiveHydrationStrategy;
use GeneratedHydratorTestAsset\BaseClass;
use GeneratedHydratorTestAsset\ClassWithMixedProperties;
use GeneratedHydratorTestAsset\ClassWithObjectProperty;
use GeneratedHydratorTestAsset\ClassWithPrivateObjectProperty;
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
use Zend\Hydrator\AbstractHydrator;
use Zend\Hydrator\HydratorInterface;
use Zend\Hydrator\Strategy\ClosureStrategy;
use function get_class;
use function ksort;

/**
 * Tests for {@see \GeneratedHydrator\ClassGenerator\HydratorGenerator} produced objects
 * @group Functional
 */
class AbstractHydratorFunctionalTest extends TestCase
{
    /**
     * @dataProvider getHydratorClasses
     */
    public function testHydrator(object $instance): void
    {
        $reflection = new ReflectionClass($instance);
        $initialData = [];
        $newData = [];

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

    /**
     * Recursively populate the $initialData and $newData array browsing the
     * full class hierarchy tree
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
    ): void {
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
            $newData[$propertyName] = $property->getName() . '__new__value';
        }
    }

    /**
     * Generates a hydrator for the given class name, and retrieves its class name
     */
    private function generateHydrator(object $instance): HydratorInterface
    {
        $parentClassName = get_class($instance);
        $generatedClassName = __NAMESPACE__ . '\\' . UniqueIdentifierGenerator::getIdentifier('Foo');
        $config = new Configuration($parentClassName);
        $config->setHydratorGenerator(new AbstractHydratorGenerator());
        /** @var ClassNameInflectorInterface|MockObject $inflector */
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

    /**
     * Recursively populate the $inspectedData array browsing the full class
     * hierarchy tree
     * Private properties from parent class that are hidden by children will be
     * dropped from the populated arrays
     *
     * @param mixed[] $inspectionData
     */
    private function recursiveFindInspectionData(
        ReflectionClass $class,
        object $instance,
        array &$inspectionData
    ): void {
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

    public function testHydratingNull(): void
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
     * @requires PHP >= 7.4
     */
    public function testHydratorWillNotRaisedUnitiliazedTypedPropertyAccessError(): void
    {
        $instance = new ClassWithTypedProperties();
        $hydrator = $this->generateHydrator($instance);

        $hydrator->hydrate(['property2' => 3], $instance);

        self::assertSame([
            'property0' => 1, // 'property0' has a default value, it should keep it.
            'property1' => 2, // 'property1' has a default value, it should keep it.
            'property2' => 3,
            'property3' => null, // 'property3' is not required, it should remain null.
            'property4' => null, // 'property4' default value is null, it should remain null.
            'untyped0' => null, // 'untyped0' is null by default
            'untyped1' => null, // 'untyped1' is null by default
        ], $hydrator->extract($instance));
    }

    /**
     * @requires PHP >= 7.4
     */
    public function testHydratorWillSetAllTypedProperties(): void
    {
        $instance = new ClassWithTypedProperties();
        $hydrator = $this->generateHydrator($instance);

        $reference = [
            'property0' => 11,
            'property1' => null, // Ensure explicit set null works as expected.
            'property2' => 13,
            'property3' => null, // Different use case (unrequired value with no default value).
            'property4' => 19,
            'untyped0' => null, // 'untyped0' is null by default
            'untyped1' => null, // 'untyped1' is null by default
        ];

        $hydrator->hydrate($reference, $instance);

        self::assertSame($reference, $hydrator->extract($instance));
    }

    public function testHydratorWillHydrateWithStrategies(): void
    {
        $instance = new ClassWithObjectProperty();

        $nestedHydrator = $this->generateHydrator(new ClassWithPublicProperties());

        /** @var AbstractHydrator $hydrator */
        $hydrator = $this->generateHydrator($instance);
        $hydrator->addStrategy('publicProperties', new RecursiveHydrationStrategy($nestedHydrator, ClassWithPublicProperties::class));
        $hydrator->addStrategy('publicPropertiesCollection', new RecursiveHydrationStrategy($nestedHydrator, ClassWithPublicProperties::class, true));

        $reference = [
            'publicProperties' => [
                'property0' => 'Prop0',
                'property1' => 'Prop1',
                'property2' => 'Prop2',
                'property3' => 'Prop3',
                'property4' => 'Prop4',
                'property5' => 'Prop5',
                'property6' => 'Prop6',
                'property7' => 'Prop7',
                'property8' => 'Prop8',
                'property9' => 'Prop9',
            ],
            'publicPropertiesCollection' => [
                [
                    'property0' => 'Prop10',
                    'property1' => 'Prop11',
                    'property2' => 'Prop12',
                    'property3' => 'Prop13',
                    'property4' => 'Prop14',
                    'property5' => 'Prop15',
                    'property6' => 'Prop16',
                    'property7' => 'Prop17',
                    'property8' => 'Prop18',
                    'property9' => 'Prop19',
                ],
                [
                    'property0' => 'Prop20',
                    'property1' => 'Prop21',
                    'property2' => 'Prop22',
                    'property3' => 'Prop23',
                    'property4' => 'Prop24',
                    'property5' => 'Prop25',
                    'property6' => 'Prop26',
                    'property7' => 'Prop27',
                    'property8' => 'Prop28',
                    'property9' => 'Prop29',
                ]
            ],
        ];

        $hydrator->hydrate($reference, $instance);

        self::assertSame($reference, $hydrator->extract($instance));
    }

    public function testHydratorWillHydratePrivatePropertiesWithStrategies(): void
    {
        $instance = new ClassWithPrivateObjectProperty();

        $nestedHydrator = $this->generateHydrator(new ClassWithPrivateProperties());

        /** @var AbstractHydrator $hydrator */
        $hydrator = $this->generateHydrator($instance);
        $hydrator->addStrategy('privateProperties', new RecursiveHydrationStrategy($nestedHydrator, ClassWithPrivateProperties::class));
        $hydrator->addStrategy('privatePropertiesCollection', new RecursiveHydrationStrategy($nestedHydrator, ClassWithPrivateProperties::class, true));

        $reference = [
            'privateProperties' => [
                'property0' => 'Prop0',
                'property1' => 'Prop1',
                'property2' => 'Prop2',
                'property3' => 'Prop3',
                'property4' => 'Prop4',
                'property5' => 'Prop5',
                'property6' => 'Prop6',
                'property7' => 'Prop7',
                'property8' => 'Prop8',
                'property9' => 'Prop9',
            ],
            'privatePropertiesCollection' => [
                [
                    'property0' => 'Prop10',
                    'property1' => 'Prop11',
                    'property2' => 'Prop12',
                    'property3' => 'Prop13',
                    'property4' => 'Prop14',
                    'property5' => 'Prop15',
                    'property6' => 'Prop16',
                    'property7' => 'Prop17',
                    'property8' => 'Prop18',
                    'property9' => 'Prop19',
                ],
                [
                    'property0' => 'Prop20',
                    'property1' => 'Prop21',
                    'property2' => 'Prop22',
                    'property3' => 'Prop23',
                    'property4' => 'Prop24',
                    'property5' => 'Prop25',
                    'property6' => 'Prop26',
                    'property7' => 'Prop27',
                    'property8' => 'Prop28',
                    'property9' => 'Prop29',
                ]
            ],
        ];

        $hydrator->hydrate($reference, $instance);

        self::assertSame($reference, $hydrator->extract($instance));
    }

    /**
     * @return mixed[]
     */
    public function getHydratorClasses(): array
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
}
