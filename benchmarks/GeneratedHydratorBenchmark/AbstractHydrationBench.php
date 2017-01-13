<?php

namespace GeneratedHydratorBenchmark;

use GeneratedHydrator\Configuration;

abstract class AbstractHydrationBench
{
    protected $hydrator;
    protected $data;

    protected function createHydrator($class)
    {
        $config        = new Configuration($class);
        $hydratorClass = $config->createFactory()->getHydratorClass();

        $this->hydrator = new $hydratorClass();
    }

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
