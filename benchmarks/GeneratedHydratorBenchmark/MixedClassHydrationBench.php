<?php

declare(strict_types=1);

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
        $this->object = new MixedClass();
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
