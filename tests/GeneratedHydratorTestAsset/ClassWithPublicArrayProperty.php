<?php

declare(strict_types=1);

namespace GeneratedHydratorTestAsset;

/**
 * Base test class to verify that class generation will actually modify the array keys of
 * public properties that keep an array
 */
class ClassWithPublicArrayProperty
{
    /** @var mixed[] */
    public $arrayProperty = [];
}
