<?php

declare(strict_types=1);

namespace GeneratedHydratorTest\ClosureGenerator\FileCache;

use GeneratedHydrator\ClosureGenerator\FileCache\GenerateFileCacheHydrator;
use GeneratedHydratorTestAsset\BaseClass;
use GeneratedHydratorTestAsset\ClassWithPrivatePropertiesAndParents;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionException;
use function get_class;

class GenerateHydratorTest extends TestCase
{
    /** @var GenerateFileCacheHydrator */
    private $generateHydrator;

    public function setUp() : void
    {
        parent::setUp();
        $this->generateHydrator = new GenerateFileCacheHydrator('build/');
    }

    /**
     * @throws ReflectionException
     */
    public function testDefaultBaseClass() : void
    {
        $hydrate = ($this->generateHydrator)(BaseClass::class);
        $object  = new BaseClass();

        $result = $hydrate(
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
