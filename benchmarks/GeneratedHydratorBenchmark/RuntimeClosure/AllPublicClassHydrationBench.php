<?php

declare(strict_types=1);

namespace GeneratedHydratorBenchmark\RuntimeClosure;

use GeneratedHydratorBenchmark\Data\AllPublicClass;

/**
 * Benchmark class that contains only public properties hydration
 *
 * @BeforeMethods({"setUp"})
 */
class AllPublicClassHydrationBench extends HydrationBench
{
    public function setUp() : void
    {
        $this->createHydrator(AllPublicClass::class);
        $this->createData();
        $this->object = new AllPublicClass();
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
