<?php

namespace GeneratedHydratorBenchmark;

/**
 * Benchmark class that contains only protected properties hydration
 *
 * @BeforeMethods({"setUp"})
 */
class AllProtectedClassHydrationBench extends AbstractHydrationBench
{
    public function setUp()
    {
        $this->createHydrator(AllProtectedClass::class);
        $this->createData();
    }

    /**
     * @Revs(100)
     * @Iterations(200)
     */
    public function benchConsume()
    {
        $object = new AllProtectedClass();
        $this->hydrator->hydrate($this->data, $object);
    }
}
