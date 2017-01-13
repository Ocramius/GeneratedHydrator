<?php

namespace GeneratedHydratorBenchmark;

use GeneratedHydrator\Configuration;
use Zend\Hydrator\HydratorInterface;

/**
 * Default base class for hydration benchmarks
 */
abstract class AbstractHydrationBench
{
    /**
     * @var HydratorInterface
     */
    protected $hydrator;

    /**
     * @var mixed[]
     */
    protected $data;

    /**
     * @var mixed
     */
    protected $object;

    /**
     * Create and set the hydrator
     *
     * @param string $class
     */
    protected function createHydrator($class)
    {
        $config        = new Configuration($class);
        $hydratorClass = $config->createFactory()->getHydratorClass();

        $this->hydrator = new $hydratorClass();
    }

    /**
     * Populate test data array
     */
    protected function createData()
    {
        $this->data = [
            'foo' => 'some foo string',
            'bar' => 42,
            'baz' => new \DateTime(),
            'someFooProperty' => array(12, 13, 14),
            'someBarProperty' => 12354.4578,
            'someBazProperty' => new \stdClass(),
        ];
    }
}
