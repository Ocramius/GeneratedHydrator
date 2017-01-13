<?php

namespace GeneratedHydratorBenchmark;

use Zend\Hydrator\HydratorInterface;

/**
 * Benchmark class that contains all benchmarks for zend code
 *
 * @BeforeMethods({"setUp"})
 */
class ZendHydrationBench
{
    /**
     * @var HydratorInterface
     */
    private $classMethodHydrator;

    /**
     * @var HydratorInterface
     */
    private $reflectionHydrator;

    /**
     * @var array
     */
    private $data;

    private $object1;
    private $object2;
    private $object3;
    private $object4;
    private $object5;
    private $object6;

    public function setUp()
    {
        $this->classMethodHydrator = new \Zend\Hydrator\ClassMethods();
        $this->reflectionHydrator = new \Zend\Hydrator\Reflection();

        $this->object1 = new AllPrivateClass();
        $this->object2 = new AllProtectedClass();
        $this->object3 = new AllPublicClass();
        $this->object4 = new InheritanceClass();
        $this->object5 = new InheritanceDeepClass();
        $this->object6 = new MixedClass();

        $this->createData();
    }

    /**
     * Populate test data array
     */
    private function createData()
    {
        $this->data = [
            'foo' => 'some foo string',
            'bar' => 42,
            'baz' => new \DateTime(),
            'someFooProperty' => array(12, 13, 14),
            'someBarProperty' => 12354.4578,
            'someBazProperty' => new \stdClass(),
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

    /**
     * @Revs(100)
     * @Iterations(200)
     */
    public function benchMethodAllPrivate()
    {
        $this->classMethodHydrator->hydrate($this->data, $this->object1);
    }

    /**
     * @Revs(100)
     * @Iterations(200)
     */
    public function benchMethodAllProtected()
    {
        $this->classMethodHydrator->hydrate($this->data, $this->object2);
    }

    /**
     * @Revs(100)
     * @Iterations(200)
     */
    public function benchMethodAllPublic()
    {
        $this->classMethodHydrator->hydrate($this->data, $this->object3);
    }

    /**
     * @Revs(100)
     * @Iterations(200)
     */
    public function benchMethodInheritance()
    {
        $this->classMethodHydrator->hydrate($this->data, $this->object4);
    }

    /**
     * @Revs(100)
     * @Iterations(200)
     */
    public function benchMethodInheritanceDeep()
    {
        $this->classMethodHydrator->hydrate($this->data, $this->object5);
    }

    /**
     * @Revs(100)
     * @Iterations(200)
     */
    public function benchMethodMixed()
    {
        $this->classMethodHydrator->hydrate($this->data, $this->object6);
    }

    /**
     * @Revs(100)
     * @Iterations(200)
     */
    public function benchReflectionAllPrivate()
    {
        $this->reflectionHydrator->hydrate($this->data, $this->object1);
    }

    /**
     * @Revs(100)
     * @Iterations(200)
     */
    public function benchReflectionAllProtected()
    {
        $this->reflectionHydrator->hydrate($this->data, $this->object2);
    }

    /**
     * @Revs(100)
     * @Iterations(200)
     */
    public function benchReflectionAllPublic()
    {
        $this->reflectionHydrator->hydrate($this->data, $this->object3);
    }

    /**
     * @Revs(100)
     * @Iterations(200)
     */
    public function benchReflectionInheritance()
    {
        $this->reflectionHydrator->hydrate($this->data, $this->object4);
    }

    /**
     * @Revs(100)
     * @Iterations(200)
     */
    public function benchReflectionInheritanceDeep()
    {
        $this->reflectionHydrator->hydrate($this->data, $this->object5);
    }

    /**
     * @Revs(100)
     * @Iterations(200)
     */
    public function benchReflectionMixed()
    {
        $this->reflectionHydrator->hydrate($this->data, $this->object6);
    }
}
