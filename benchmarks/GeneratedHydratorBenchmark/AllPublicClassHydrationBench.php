<?php

namespace GeneratedHydratorBenchmark;

/**
 * Benchmark class that contains only public properties hydration
 *
 * @BeforeMethods({"setUp"})
 */
class AllPublicClassHydrationBench extends AbstractHydrationBench
{
    public function setUp()
    {
        $this->createHydrator(AllPublicClass::class);
        $this->createData();
    }

    /**
     * @Revs(100)
     * @Iterations(200)
     */
    public function benchConsume()
    {
        $object = new AllPublicClass();
        $this->hydrator->hydrate($this->data, $object);
    }
}
