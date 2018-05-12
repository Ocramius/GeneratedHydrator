<?php

declare(strict_types=1);

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
        $this->object = new AllPublicClass();
    }

    /**
     * @Revs(100)
     * @Iterations(200)
     */
    public function benchConsume()
    {
        $this->hydrator->hydrate($this->data, $this->object);
    }
}
