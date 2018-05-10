<?php

declare(strict_types=1);

namespace GeneratedHydratorTestAsset;

/**
 * Base test class to play around with pre-existing magic methods
 */
class ClassWithMagicMethods
{
    /**
     * {@inheritDoc}
     */
    public function __set($name, $value)
    {
        return [$name => $value];
    }

    /**
     * {@inheritDoc}
     */
    public function __get($name)
    {
        return $name;
    }

    /**
     * {@inheritDoc}
     */
    public function __isset($name)
    {
        return (bool) $name;
    }

    /**
     * {@inheritDoc}
     */
    public function __unset($name)
    {
        return (bool) $name;
    }

    /**
     * {@inheritDoc}
     */
    public function __sleep()
    {
    }

    /**
     * {@inheritDoc}
     */
    public function __wakeup()
    {
    }
}
