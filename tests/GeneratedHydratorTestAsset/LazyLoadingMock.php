<?php

declare(strict_types=1);

namespace GeneratedHydratorTestAsset;

/**
 * Base test class to catch instantiations of lazy loading objects
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
class LazyLoadingMock
{
    /**
     * @var mixed
     */
    public $initializer;

    /**
     * @param mixed $initializer
     */
    public function __construct($initializer)
    {
        $this->initializer = $initializer;
    }
}
