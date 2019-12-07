<?php

declare(strict_types=1);

namespace GeneratedHydratorTest\ClosureGenerator\Runtime;

use GeneratedHydrator\ClosureGenerator\GenerateHydrator;
use GeneratedHydrator\ClosureGenerator\Runtime\GenerateRuntimeExtractor;
use GeneratedHydratorTestAsset\BaseClass;
use GeneratedHydratorTestAsset\ClassWithPrivatePropertiesAndParents;
use GeneratedHydratorTestAsset\ClassWithStaticProperties;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use stdClass;
use function get_class;

class GenerateExtractorTest extends TestCase
{
    /** @var GenerateHydrator */
    private $generateExtractor;

    public function setUp() : void
    {
        parent::setUp();
        $this->generateExtractor = new GenerateRuntimeExtractor();
    }

    /**
     * @throws ReflectionException
     */
    public function testDefaultBaseClass() : void
    {
        $object    = new BaseClass();
        $extractor = ($this->generateExtractor)(get_class($object));

        $result = $extractor($object);

        self::assertEquals(
            [
                'publicProperty' => 'publicPropertyDefault',
                'protectedProperty' => 'protectedPropertyDefault',
                'privateProperty' => 'privatePropertyDefault',
            ],
            $result
        );
    }

    /**
     * @throws ReflectionException
     */
    public function testClassWithParents() : void
    {
        $object    = new ClassWithPrivatePropertiesAndParents();
        $extractor = ($this->generateExtractor)(get_class($object));

        $result = $extractor($object);

        self::assertEquals(
            [
                'property0' => 'property0_fromChild',
                'property1' => 'property1_fromChild',
                'property2' => 'property2',
                'property3' => 'property3',
                'property4' => 'property4',
                'property5' => 'property5',
                'property6' => 'property6',
                'property7' => 'property7',
                'property8' => 'property8',
                'property9' => 'property9',
                'property20' => 'property20_fromChild',
                'property21' => 'property21',
                'property22' => 'property22',
                'property30' => 'property30',
                'property31' => 'property31',
                'property32' => 'property32',
            ],
            $result
        );
    }

    /**
     * @throws ReflectionException
     */
    public function testStdClass() : void
    {
        $object    = new stdClass();
        $extractor = ($this->generateExtractor)(stdClass::class);

        $result = $extractor($object);

        self::assertEquals([], $result);
    }

    /**
     * @throws ReflectionException
     */
    public function testClassWithStaticProperties() : void
    {
        $object    = new ClassWithStaticProperties();
        $extractor = ($this->generateExtractor)(ClassWithStaticProperties::class);

        $result = $extractor($object);

        self::assertEquals(
            [
                'private' => null,
                'protected' => null,
                'public' => null,
            ],
            $result
        );
    }

    /**
     * @throws ReflectionException
     */
    public function testCache() : void
    {
        $object    = new BaseClass();
        $extractor = ($this->generateExtractor)(get_class($object));

        self::assertSame($extractor, ($this->generateExtractor)(get_class($object)));
    }
}
