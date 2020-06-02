<?php

declare(strict_types=1);

namespace GeneratedHydrator\Strategy;

use Zend\Hydrator\HydratorInterface;
use Zend\Hydrator\Strategy\StrategyInterface;

class RecursiveHydrationStrategy implements StrategyInterface
{
    /**
     * @var HydratorInterface
     */
    private HydratorInterface $hydrator;

    /**
     * @var string
     */
    private string $className;

    /**
     * @var bool
     */
    private bool $isCollection;

    public function __construct(HydratorInterface $hydrator, string $className, bool $isCollection = false)
    {
        $this->hydrator = $hydrator;
        $this->className = $className;
        $this->isCollection = $isCollection;
    }

    /**
     * @param mixed $value
     *
     * @return array|mixed
     */
    public function extract($value)
    {
        if (!$this->isCollection) {
            return $this->extractObject($value);
        }

        if (!is_array($value)) {
            $value = [$value];
        }

        $collection = [];

        foreach ($value as $item) {
            $collection[] = $this->extractObject($item);
        }

        return $collection;
    }

    /**
     * @param mixed $value
     *
     * @return mixed|object
     */
    public function hydrate($value)
    {
        if (!$this->isCollection) {
            return $this->hydrateObject($value);
        }

        if (!is_array($value)) {
            $value = [$value];
        }

        $collection = [];

        foreach ($value as $item) {
            $collection[] = $this->hydrateObject($item);
        }

        return $collection;
    }

    /**
     * @param mixed $value
     *
     * @return array|mixed
     */
    private function extractObject($value)
    {
        if (!$value instanceof $this->className) {
            throw new \InvalidArgumentException('The $value is not an instance of "' . $this->className . '".');
        }

        return $this->hydrator->extract($value);
    }

    /**
     * @param mixed $value
     *
     * @return object|mixed
     */
    private function hydrateObject($value)
    {
        $instance = new $this->className();

        return $this->hydrator->hydrate($value, $instance);
    }
}
