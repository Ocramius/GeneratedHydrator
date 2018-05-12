<?php

declare(strict_types=1);

namespace GeneratedHydratorBenchmark;

use Zend\Hydrator\ClassMethods;
use Zend\Hydrator\HydratorInterface;
use Zend\Hydrator\Reflection;

/**
 * Benchmark class that contains all benchmarks for zend code
 *
 * @BeforeMethods({"setUp"})
 */
class ZendHydrationBench
{
    /** @var HydratorInterface */
    private $classMethodHydrator;

    /** @var HydratorInterface */
    private $reflectionHydrator;

    /** @var mixed[] */
    private $data;

    /** @var object */
    private $object1;
    /** @var object */
    private $object2;
    /** @var object */
    private $object3;
    /** @var object */
    private $object4;
    /** @var object */
    private $object5;
    /** @var object */
    private $object6;

    public function setUp()
    {
        $this->classMethodHydrator = new ClassMethods();
        $this->reflectionHydrator  = new Reflection();

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
            'someFooProperty' => [12, 13, 14],
            'someBarProperty' => 12354.4578,
            'someBazProperty' => new \stdClass(),
            'foo1' => 'some foo string',
            'bar1' => 42,
            'baz1' => new \DateTime(),
            'someFooProperty1' => [12, 13, 14],
            'someBarProperty1' => 12354.4578,
            'someBazProperty1' => new \stdClass(),
            'foo2' => 'some foo string',
            'bar2' => 42,
            'baz2' => new \DateTime(),
            'someFooProperty2' => [12, 13, 14],
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
