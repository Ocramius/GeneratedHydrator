<?php

declare(strict_types=1);

namespace GeneratedHydratorBenchmark\ClassGenerator;

use GeneratedHydratorBenchmark\Data\MixedClass;
use GeneratedHydratorBenchmark\HydrationBench;

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
        $this->hydrator->hydrate($this->data, $this->object);
    }
}
