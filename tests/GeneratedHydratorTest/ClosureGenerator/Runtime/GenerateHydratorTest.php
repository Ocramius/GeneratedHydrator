<?php

declare(strict_types=1);

namespace GeneratedHydratorTest\ClosureGenerator\Runtime;

use GeneratedHydrator\ClosureGenerator\Runtime\GenerateRuntimeHydrator;
use GeneratedHydratorTestAsset\BaseClass;
use GeneratedHydratorTestAsset\ClassWithPrivatePropertiesAndParents;
use GeneratedHydratorTestAsset\ClassWithStaticProperties;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionException;
use stdClass;
use function get_class;

class GenerateHydratorTest extends TestCase
{
    /** @var GenerateRuntimeHydrator */
    private $generateHydrator;

    public function setUp() : void
    {
        parent::setUp();
        $this->generateHydrator = new GenerateRuntimeHydrator();
    }

    /**
     * @throws ReflectionException
     */
    public function testDefaultBaseClass() : void
    {
        $object   = new BaseClass();
        $hydrator = ($this->generateHydrator)(get_class($object));

        $result = $hydrator(
            [
                'publicProperty' => 'publicPropertyNew',
                'protectedProperty' => 'protectedPropertyNew',
                'privateProperty' => 'privatePropertyNew',
            ],
            $object
        );

        self::assertSame(
            [
                'publicProperty' => 'publicPropertyNew',
                'protectedProperty' => 'protectedPropertyNew',
                'privateProperty' => 'privatePropertyNew',
            ],
            $this->getProperties($result)
        );
    }

    /**
     * @throws ReflectionException
     */
    public function testPartialBaseClass() : void
    {
        $object   = new BaseClass();
        $hydrator = ($this->generateHydrator)(get_class($object));

        $result = $hydrator(['privateProperty' => 'privatePropertyNew'], $object);

        self::assertSame(
            [
                'publicProperty' => 'publicPropertyDefault',
                'protectedProperty' => 'protectedPropertyDefault',
                'privateProperty' => 'privatePropertyNew',
            ],
            $this->getProperties($result)
        );
    }

    /**
     * @throws ReflectionException
     */
    public function testClassWithParents() : void
    {
        $object  = new ClassWithPrivatePropertiesAndParents();
        $hydrate = ($this->generateHydrator)(get_class($object));

        $hydrate(
            [
                'property0' => 'property0_new',
                'property1' => 'property1_new',
                'property2' => 'property2_new',
                'property3' => 'property3_new',
                'property4' => 'property4_new',
                'property5' => 'property5_new',
                'property6' => 'property6_new',
                'property7' => 'property7_new',
                'property8' => 'property8_new',
                'property9' => 'property9_new',
                'property20' => 'property20_new',
                'property21' => 'property21_new',
                'property22' => 'property22_new',
                'property30' => 'property30_new',
                'property31' => 'property31_new',
                'property32' => 'property32_new',
            ],
            $object
        );
        self::assertSame(
            [
                'property0' => 'property0_new',
                'property1' => 'property1_new',
                'property2' => 'property2_new',
                'property3' => 'property3_new',
                'property4' => 'property4_new',
                'property5' => 'property5_new',
                'property6' => 'property6_new',
                'property7' => 'property7_new',
                'property8' => 'property8_new',
                'property9' => 'property9_new',
                'property20' => 'property20_new',
                'property21' => 'property21_new',
                'property22' => 'property22_new',
                'property30' => 'property30_new',
                'property31' => 'property31_new',
                'property32' => 'property32_new',
            ],
            $this->getProperties($object)
        );
    }

    /**
     * @throws ReflectionException
     */
    public function testPartialClassWithParents() : void
    {
        $object  = new ClassWithPrivatePropertiesAndParents();
        $hydrate = ($this->generateHydrator)(get_class($object));

        $hydrate(
            [
                'property0' => 'property0_new',
                'property3' => 'property3_new',
                'property20' => 'property20_new',
                'property22' => 'property22_new',
                'property30' => 'property30_new',
            ],
            $object
        );
        self::assertSame(
            [
                'property0' => 'property0_new',
                'property1' => 'property1_fromChild',
                'property2' => 'property2',
                'property3' => 'property3_new',
                'property4' => 'property4',
                'property5' => 'property5',
                'property6' => 'property6',
                'property7' => 'property7',
                'property8' => 'property8',
                'property9' => 'property9',
                'property20' => 'property20_new',
                'property21' => 'property21',
                'property22' => 'property22_new',
                'property30' => 'property30_new',
                'property31' => 'property31',
                'property32' => 'property32',
            ],
            $this->getProperties($object)
        );
    }

    /**
     * @throws ReflectionException
     */
    public function testStdClass() : void
    {
        $object  = new stdClass();
        $hydrate = ($this->generateHydrator)(stdClass::class);

        $hydrate(['property' => 'property'], $object);

        self::assertEquals([], $this->getProperties($object));
    }

    /**
     * @throws ReflectionException
     */
    public function testClassWithStaticProperties() : void
    {
        $object  = new ClassWithStaticProperties();
        $hydrate = ($this->generateHydrator)(ClassWithStaticProperties::class);

        $hydrate(
            [
                'privateStatic' => 'privateStaticNew',
                'protectedStatic' => 'protectedStaticNew',
                'publicStatic' => 'publicStaticNew',
                'private' => 'privateNew',
                'protected' => 'protectedNew',
                'public' => 'publicNew',
            ],
            $object
        );

        self::assertEquals(
            [
                'privateStatic' => null,
                'protectedStatic' => null,
                'publicStatic' => null,
                'private' => 'privateNew',
                'protected' => 'protectedNew',
                'public' => 'publicNew',
            ],
            $object->getStaticProperties(),
        );
    }

    /**
     * @throws ReflectionException
     */
    public function testCache() : void
    {
        $object    = new BaseClass();
        $extractor = ($this->generateHydrator)(get_class($object));

        self::assertSame($extractor, ($this->generateHydrator)(get_class($object)));
    }

    /**
     * @return mixed[]
     *
     * @throws ReflectionException
     */
    private function getProperties(object $object) : array
    {
        $reflectionClass = new ReflectionClass($object);

        return $this->getPropertiesWithReflection($object, $reflectionClass);
    }

    /**
     * @return mixed[]
     */
    private function getPropertiesWithReflection(object $object, ReflectionClass $reflectionClass) : array
    {
        $properties = $reflectionClass->getParentClass()
            ? $this->getPropertiesWithReflection($object, $reflectionClass->getParentClass())
            : [];
        foreach ($reflectionClass->getProperties() as $reflectionProperty) {
            if ($reflectionProperty->isStatic()) {
                continue;
            }
            if ($reflectionProperty->isPrivate() || $reflectionProperty->isProtected()) {
                $reflectionProperty->setAccessible(true);
            }
            $properties[$reflectionProperty->getName()] = $reflectionProperty->getValue($object);
        }

        return $properties;
    }
}
