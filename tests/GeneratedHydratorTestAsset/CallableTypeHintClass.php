<?php

declare(strict_types=1);

namespace GeneratedHydratorTestAsset;

/**
 * Class with a callable type hint in a method - used to test callable type hint
 * generation
 */
class CallableTypeHintClass
{
    public function callableTypeHintMethod(callable $parameter): callable
    {
        return $parameter;
    }
}
