<?php

namespace GeneratedHydratorBenchmark;

/**
 * @BeforeMethods({"setUp"})
 */
class InheritanceDeepClassHydrationBench extends AbstractHydrationBench
{
    protected function createData()
    {
        parent::createData();

        $this->data += [
            'foo1' => 'some foo string',
            'bar1' => 42,
            'baz1' => new \DateTime(),
            'someFooProperty1' => array(12, 13, 14),
            'someBarProperty1' => 12354.4578,
            'someBazProperty1' => new \stdClass(),
            'foo2' => 'some foo string',
            'bar2' => 42,
            'baz2' => new \DateTime(),
            'someFooProperty2' => array(12, 13, 14),
            'someBarProperty2' => 12354.4578,
            'someBazProperty2' => new \stdClass(),
        ];
    }

    public function setUp()
    {
        $this->createHydrator(InheritanceDeepClass::class);
        $this->createData();
    }

    /**
     * @Revs(100)
     * @Iterations(200)
     */
    public function benchConsume()
    {
        $object = new InheritanceDeepClass();
        $this->hydrator->hydrate($this->data, $object);
    }
}
