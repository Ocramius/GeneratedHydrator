<?php

namespace GeneratedHydratorBenchmark;

/**
 * Benchmark class that contains public, protected and private properties hydration
 *
 * @BeforeMethods({"setUp"})
 */
class MixedClassHydrationBench extends AbstractHydrationBench
{
    public function setUp()
    {
        $this->createHydrator(MixedClass::class);
        $this->createData();
    }

    /**
     * @Revs(100)
     * @Iterations(200)
     */
    public function benchConsume()
    {
        $object = new MixedClass();
        $this->hydrator->hydrate($this->data, $object);
    }
}
