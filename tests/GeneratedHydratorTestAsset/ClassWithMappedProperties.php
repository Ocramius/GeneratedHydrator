<?php

declare(strict_types=1);

namespace GeneratedHydratorTestAsset;

use GeneratedHydrator\Annotation\MappedFrom;

final class ClassWithMappedProperties
{
    /** @MappedFrom(name="test0") */
    public int $property0;
    /** @MappedFrom(name="test1") */
    public ?int $property1;
    /** @MappedFrom(name="test2") */
    public int $property2 = 3;
    /** @MappedFrom(name="test3") */
    public $property3;
}
