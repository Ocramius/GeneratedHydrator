<?php

declare(strict_types=1);

namespace GeneratedHydratorTestAsset;

/**
 * Base test class to play around with public properties
 */
class ClassWithPrivateObjectProperty
{
    /** @var ClassWithPrivateProperties|null */
    private $privateProperties;

    /** @var ClassWithPrivateProperties[] */
    private array $privatePropertiesCollection = [];
}
