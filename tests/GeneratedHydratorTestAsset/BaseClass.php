<?php

declare(strict_types=1);

namespace GeneratedHydratorTestAsset;

use stdClass;

/**
 * Base test class with various intercepted properties
 */
class BaseClass implements Base
{
    /** @var string */
    public $publicProperty = 'publicPropertyDefault';

    /** @var string */
    protected $protectedProperty = 'protectedPropertyDefault';

//phpcs:disable SlevomatCodingStandard.Classes.UnusedPrivateElements.UnusedProperty
    /** @var string */
    private $privateProperty = 'privatePropertyDefault';
//phpcs:enable

    public function publicMethod() : string
    {
        return 'publicMethodDefault';
    }

    protected function protectedMethod() : string
    {
        return 'protectedMethodDefault';
    }

//phpcs:disable SlevomatCodingStandard.Classes.UnusedPrivateElements.UnusedMethod
    private function privateMethod() : string
    {
//phpcs:enable
        return 'privateMethodDefault';
    }

    public function publicTypeHintedMethod(stdClass $param) : string
    {
        return 'publicTypeHintedMethodDefault';
    }

    /**
     * @param mixed[] $param
     */
    public function publicArrayHintedMethod(array $param) : string
    {
        return 'publicArrayHintedMethodDefault';
    }

    public function & publicByReferenceMethod() : string
    {
        return 'publicByReferenceMethodDefault';
    }

    /**
     * @param mixed $param
     * @param mixed $byRefParam
     */
    public function publicByReferenceParameterMethod($param, &$byRefParam) : string
    {
        return 'publicByReferenceParameterMethodDefault';
    }
}
