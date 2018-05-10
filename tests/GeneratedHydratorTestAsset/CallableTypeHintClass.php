<?php

declare(strict_types=1);

namespace GeneratedHydratorTestAsset;

/**
 * Class with a callable type hint in a method - used to test callable type hint
 * generation
 */
class CallableTypeHintClass
{
    /**
     * @param callable $parameter
     *
     * @return callable
     */
    public function callableTypeHintMethod(callable $parameter)
    {
        return $parameter;
    }
}
