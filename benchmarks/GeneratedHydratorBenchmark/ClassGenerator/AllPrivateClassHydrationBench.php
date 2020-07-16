<?php

declare(strict_types=1);

namespace GeneratedHydratorBenchmark\ClassGenerator;

use GeneratedHydratorBenchmark\Data\AllPrivateClass;
use GeneratedHydratorBenchmark\HydrationBench;

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
        $this->hydrator->hydrate($this->data, $this->object);
    }
}
