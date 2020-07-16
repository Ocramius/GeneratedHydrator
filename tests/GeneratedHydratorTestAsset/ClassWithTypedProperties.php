<?php

declare(strict_types=1);

namespace GeneratedHydratorTestAsset;

use GeneratedHydrator\Annotation\MappedFrom;

/**
 * Test PHP 7.4 hydration and dehydration.
 */
final class ClassWithTypedProperties
{
    private int $property0  = 1;
    private ?int $property1 = 2;
    private int $property2;
    private ?int $property3;
    private ?int $property4 = null;
    /** @var mixed */
    private $untyped0;
    /** @var mixed */
    private $untyped1 = null;
    /** @MappedFrom(name="property_5") */
    private string $property5 = 'test';
}
