<?php

declare(strict_types=1);

namespace GeneratedHydratorBenchmark;

/**
 * Benchmark class that contains only protected properties hydration
 *
 * @BeforeMethods({"setUp"})
 */
class AllProtectedClassHydrationBench extends AbstractHydrationBench
{
    public function setUp() : void
    {
        $this->createHydrator(AllProtectedClass::class);
        $this->createData();
        $this->object = new AllProtectedClass();
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
