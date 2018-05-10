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

    /** @var mixed */
//phpcs:disable SlevomatCodingStandard.Classes.UnusedPrivateElements.UnusedProperty
    private $baz = 3;
//phpcs:enable

    /**
     * Method to be disabled
     */
    public function doFoo()
    {
    }

    /**
     * @param string $name
     *
     * @return mixed
     */
    public function __get($name)
    {
        return $this->$name;
    }
}
