<?php

declare(strict_types=1);

namespace GeneratedHydratorTestAsset;

/**
 * Base test class to catch instantiations of lazy loading objects
 */
class LazyLoadingMock
{
    public function __construct(public mixed $initializer)
    {
    }
}
