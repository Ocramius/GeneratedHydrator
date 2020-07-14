<?php

declare(strict_types=1);

namespace GeneratedHydratorBenchmark;

use DateTime;
use stdClass;
use Laminas\Hydrator\ClassMethods;
use Laminas\Hydrator\HydratorInterface;
use Laminas\Hydrator\Reflection;

/**
 * Benchmark class that contains all benchmarks for Laminas code
 *
 * @BeforeMethods({"setUp"})
 */
class LaminasHydrationBench
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

    public function setUp() : void
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
    private function createData() : void
    {
        $this->data = [
            'foo' => 'some foo string',
            'bar' => 42,
            'baz' => new DateTime(),
            'someFooProperty' => [12, 13, 14],
            'someBarProperty' => 12354.4578,
            'someBazProperty' => new stdClass(),
            'foo1' => 'some foo string',
            'bar1' => 42,
            'baz1' => new DateTime(),
            'someFooProperty1' => [12, 13, 14],
            'someBarProperty1' => 12354.4578,
            'someBazProperty1' => new stdClass(),
            'foo2' => 'some foo string',
            'bar2' => 42,
            'baz2' => new DateTime(),
            'someFooProperty2' => [12, 13, 14],
            'someBarProperty2' => 12354.4578,
            'someBazProperty2' => new stdClass(),
        ];
    }

    /**
     * @Revs(100)
     * @Iterations(200)
     */
    public function benchMethodAllPrivate() : void
    {
        $this->classMethodHydrator->hydrate($this->data, $this->object1);
    }

    /**
     * @Revs(100)
     * @Iterations(200)
     */
    public function benchMethodAllProtected() : void
    {
        $this->classMethodHydrator->hydrate($this->data, $this->object2);
    }

    /**
     * @Revs(100)
     * @Iterations(200)
     */
    public function benchMethodAllPublic() : void
    {
        $this->classMethodHydrator->hydrate($this->data, $this->object3);
    }

    /**
     * @Revs(100)
     * @Iterations(200)
     */
    public function benchMethodInheritance() : void
    {
        $this->classMethodHydrator->hydrate($this->data, $this->object4);
    }

    /**
     * @Revs(100)
     * @Iterations(200)
     */
    public function benchMethodInheritanceDeep() : void
    {
        $this->classMethodHydrator->hydrate($this->data, $this->object5);
    }

    /**
     * @Revs(100)
     * @Iterations(200)
     */
    public function benchMethodMixed() : void
    {
        $this->classMethodHydrator->hydrate($this->data, $this->object6);
    }

    /**
     * @Revs(100)
     * @Iterations(200)
     */
    public function benchReflectionAllPrivate() : void
    {
        $this->reflectionHydrator->hydrate($this->data, $this->object1);
    }

    /**
     * @Revs(100)
     * @Iterations(200)
     */
    public function benchReflectionAllProtected() : void
    {
        $this->reflectionHydrator->hydrate($this->data, $this->object2);
    }

    /**
     * @Revs(100)
     * @Iterations(200)
     */
    public function benchReflectionAllPublic() : void
    {
        $this->reflectionHydrator->hydrate($this->data, $this->object3);
    }

    /**
     * @Revs(100)
     * @Iterations(200)
     */
    public function benchReflectionInheritance() : void
    {
        $this->reflectionHydrator->hydrate($this->data, $this->object4);
    }

    /**
     * @Revs(100)
     * @Iterations(200)
     */
    public function benchReflectionInheritanceDeep() : void
    {
        $this->reflectionHydrator->hydrate($this->data, $this->object5);
    }

    /**
     * @Revs(100)
     * @Iterations(200)
     */
    public function benchReflectionMixed() : void
    {
        $this->reflectionHydrator->hydrate($this->data, $this->object6);
    }
}
