<?php

declare(strict_types=1);

namespace GeneratedHydratorBenchmark\RuntimeClosure;

use GeneratedHydratorBenchmark\Data\MixedClass;

/**
 * Benchmark class that contains public, protected and private properties hydration
 *
 * @BeforeMethods({"setUp"})
 */
class MixedClassHydrationBench extends HydrationBench
{
    public function setUp() : void
    {
        $this->createHydrator(MixedClass::class);
        $this->createData();
        $this->object = new MixedClass();
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
