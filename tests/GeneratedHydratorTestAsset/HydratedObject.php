<?php

declare(strict_types=1);

namespace GeneratedHydratorTestAsset;

/**
 * Test object to be hydrated
 */
class HydratedObject
{
    /** @var mixed */
    public $foo = 1;

    /** @var mixed */
    protected $bar = 2;

//phpcs:disable SlevomatCodingStandard.Classes.UnusedPrivateElements.UnusedProperty
    /** @var mixed */
    private $baz = 3;
//phpcs:enable

    /**
     * Method to be disabled
     */
    public function doFoo(): void
    {
    }

    /**
     * @return mixed
     */
    public function __get(string $name)
    {
        return $this->$name;
    }
}
