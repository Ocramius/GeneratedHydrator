<?php

declare(strict_types=1);

namespace GeneratedHydratorTestAsset;

/**
 * Test object to be hydrated
 */
class HydratedObject
{
    public mixed $foo = 1;

    protected mixed $bar = 2;

//phpcs:disable SlevomatCodingStandard.Classes.UnusedPrivateElements.UnusedProperty
    private mixed $baz = 3;
//phpcs:enable

    /**
     * Method to be disabled
     */
    public function doFoo(): void
    {
    }

    public function __get(string $name): mixed
    {
        return $this->$name;
    }
}
