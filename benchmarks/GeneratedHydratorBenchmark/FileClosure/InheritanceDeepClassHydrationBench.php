<?php

declare(strict_types=1);

namespace GeneratedHydratorBenchmark\RuntimeClosure;

use DateTime;
use GeneratedHydratorBenchmark\Data\InheritanceDeepClass;
use stdClass;

/**
 * Benchmark class that contains mixed deeply inherited properties hydration
 *
 * @BeforeMethods({"setUp"})
 */
class InheritanceDeepClassHydrationBench extends HydrationBench
{
    protected function createData() : void
    {
        parent::createData();

        $this->data += [
            'foo1' => 'some foo string',
            'bar1' => 42,
            'baz1' => new DateTime(),
            'someFooProperty1' => [12, 13, 14],
            'someBarProperty1' => 12354.4578,
            'someBazProperty1' => new stdClass(),
            'foo2' => 'some foo string',
            'bar2' => 42,
            'baz2' => new DateTime(),
            'someFooProperty2' => [12, 13, 14],
            'someBarProperty2' => 12354.4578,
            'someBazProperty2' => new stdClass(),
        ];
    }

    public function setUp() : void
    {
        $this->createHydrator(InheritanceDeepClass::class);
        $this->createData();
        $this->object = new InheritanceDeepClass();
    }

    /**
     * @Revs(100)
     * @Iterations(200)
     */
    public function benchConsume() : void
    {
        ($this->hydrator)($this->data, $this->object);
    }
}
