<?php

declare(strict_types=1);

namespace GeneratedHydratorTestAsset;

/**
 * Test class used to verify that the generated hydrator respects magic getters with a by-ref return value
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
class ClassWithByRefMagicMethods
{
    /**
     * {@inheritDoc}
     */
    public function & __set($name, $value)
    {
        return array($name => $value);
    }

    /**
     * {@inheritDoc}
     */
    public function & __get($name)
    {
        return $name;
    }

    /**
     * {@inheritDoc}
     */
    public function & __isset($name)
    {
        return (bool) $name;
    }

    /**
     * {@inheritDoc}
     */
    public function & __unset($name)
    {
        return (bool) $name;
    }

    /**
     * {@inheritDoc}
     */
    public function & __sleep()
    {
    }

    /**
     * {@inheritDoc}
     */
    public function & __wakeup()
    {
    }
}
