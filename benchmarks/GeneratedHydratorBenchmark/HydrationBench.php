<?php

declare(strict_types=1);

namespace GeneratedHydratorBenchmark;

use DateTime;
use GeneratedHydrator\Configuration;
use stdClass;
use Zend\Hydrator\HydratorInterface;

/**
 * Default base class for hydration benchmarks
 */
abstract class HydrationBench
{
    /** @var HydratorInterface */
    protected $hydrator;

    /** @var mixed[] */
    protected $data;

    /** @var mixed */
    protected $object;

    /**
     * Create and set the hydrator
     */
    protected function createHydrator(string $class) : void
    {
        $config        = new Configuration($class);
        $hydratorClass = $config->createFactory()->getHydratorClass();

        $this->hydrator = new $hydratorClass();
    }

    /**
     * Populate test data array
     */
    protected function createData() : void
    {
        $this->data = [
            'foo' => 'some foo string',
            'bar' => 42,
            'baz' => new DateTime(),
            'someFooProperty' => [12, 13, 14],
            'someBarProperty' => 12354.4578,
            'someBazProperty' => new stdClass(),
        ];
    }
}
