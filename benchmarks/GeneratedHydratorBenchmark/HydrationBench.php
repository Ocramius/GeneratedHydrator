<?php

declare(strict_types=1);

namespace GeneratedHydratorBenchmark;

use DateTime;
use GeneratedHydrator\Configuration;
use Laminas\Hydrator\HydratorInterface;
use stdClass;

/**
 * Default base class for hydration benchmarks
 */
abstract class HydrationBench
{
    protected HydratorInterface $hydrator;

    /** @var mixed[] */
    protected array $data;

    protected mixed $object;

    /**
     * Create and set the hydrator
     */
    protected function createHydrator(string $class): void
    {
        $config        = new Configuration($class);
        $hydratorClass = $config->createFactory()->getHydratorClass();

        $this->hydrator = new $hydratorClass();
    }

    /**
     * Populate test data array
     */
    protected function createData(): void
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
