<?php

declare(strict_types=1);

namespace GeneratedHydratorTestAsset;

/**
 * Base test class to catch instantiations of lazy loading objects
 */
class LazyLoadingMock
{
    public mixed $initializer;

    public function __construct(mixed $initializer)
    {
        $this->initializer = $initializer;
    }
}
