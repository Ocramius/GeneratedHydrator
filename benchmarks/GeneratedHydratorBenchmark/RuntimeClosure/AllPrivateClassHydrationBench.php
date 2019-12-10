<?php

declare(strict_types=1);

namespace GeneratedHydratorBenchmark\RuntimeClosure;

use GeneratedHydratorBenchmark\Data\AllPrivateClass;

/**
 * Benchmark class that contains only private properties hydration
 *
 * @BeforeMethods({"setUp"})
 */
class AllPrivateClassHydrationBench extends HydrationBench
{
    public function setUp() : void
    {
        $this->createHydrator(AllPrivateClass::class);
        $this->createData();
        $this->object = new AllPrivateClass();
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
