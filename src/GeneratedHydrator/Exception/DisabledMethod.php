<?php

declare(strict_types=1);

namespace GeneratedHydrator\Exception;

use BadMethodCallException;

use function sprintf;

/**
 * Exception for forcefully disabled methods
 */
class DisabledMethod extends BadMethodCallException implements Exception
{
    public static function create(string $method): self
    {
        return new self(sprintf('Method "%s" is forcefully disabled', $method));
    }
}
