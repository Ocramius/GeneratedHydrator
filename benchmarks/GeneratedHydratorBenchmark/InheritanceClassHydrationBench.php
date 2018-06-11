<?php

declare(strict_types=1);

namespace GeneratedHydratorBenchmark;

/**
 * Benchmark class that contains mixed inherited properties hydration
 *
 * @BeforeMethods({"setUp"})
 */
class InheritanceClassHydrationBench extends HydrationBench
{
    protected function createData() : void
    {
        parent::createData();

        $this->data += [
            'foo1' => 'some foo string',
            'bar1' => 42,
            'baz1' => new \DateTime(),
            'someFooProperty1' => [12, 13, 14],
            'someBarProperty1' => 12354.4578,
            'someBazProperty1' => new \stdClass(),
        ];
    }

    public function setUp() : void
    {
        $this->createHydrator(InheritanceClass::class);
        $this->createData();
        $this->object = new InheritanceClass();
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
