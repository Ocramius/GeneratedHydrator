<?php

declare(strict_types=1);

namespace GeneratedHydratorBenchmark\RuntimeClosure;

use DateTime;
use GeneratedHydrator\ClosureGenerator\FileCache\GenerateFileCacheHydrator;
use GeneratedHydratorBenchmark\HydrationBench as BaseHydrationBenchAlias;
use ReflectionException;
use stdClass;

class HydrationBench extends BaseHydrationBenchAlias
{
    /** @var callable */
    protected $hydrator;

    /** @var mixed[] */
    protected $data;

    /** @var mixed */
    protected $object;

    /**
     * Create and set the hydrator
     *
     * @throws ReflectionException
     */
    protected function createHydrator(string $class) : void
    {
        $this->hydrator = (new GenerateFileCacheHydrator('build/'))($class);
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
