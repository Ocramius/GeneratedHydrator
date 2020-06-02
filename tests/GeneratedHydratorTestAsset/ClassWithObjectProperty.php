<?php

declare(strict_types=1);

namespace GeneratedHydratorTestAsset;

/**
 * Base test class to play around with public properties
 */
class ClassWithObjectProperty
{
    /** @var ClassWithPublicProperties|null */
    public $publicProperties;

    /** @var ClassWithPublicProperties[] */
    public array $publicPropertiesCollection = [];
}
