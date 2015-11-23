<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license.
 */

namespace GeneratedHydratorPerformance;

use Athletic\AthleticEvent;
use CodeGenerationUtils\GeneratorStrategy\EvaluatingGeneratorStrategy;
use GeneratedHydrator\Configuration;
use ReflectionClass;
use ReflectionProperty;
use Zend\Stdlib\Hydrator\ClassMethods;
use Zend\Stdlib\Hydrator\ObjectProperty;

/**
 * Base performance test for {@see \GeneratedHydrator\ClassGenerator\HydratorGenerator} produced
 * objects
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
abstract class AbstractHydratorPerformanceAthleticEvent extends AthleticEvent
{
    /**
     * @var \Zend\Stdlib\Hydrator\HydratorInterface
     */
    protected $hydrator;

    /**
     * @var object
     */
    protected $hydratedObject;

    /**
     * @var \ReflectionProperty[] a map of accessible reflection properties
     */
    protected $reflectionProperties;

    /**
     * @var mixed[]
     */
    protected $hydrationData;

    /**
     * @var \Zend\Stdlib\Hydrator\ObjectProperty
     */
    protected $objectPropertyHydrator;

    /**
     * @var \Zend\Stdlib\Hydrator\ClassMethods
     */
    protected $classMethodsHydrator;

    /**
     * Method responsible for testing the object to test against
     *
     * @return object
     */
    abstract protected function getHydratedObject();

    /**
     * {@inheritDoc}
     */
    public function setUp()
    {
        $this->hydratedObject            = $this->getHydratedObject();
        $this->hydrator                  = $this->generateHydrator($this->hydratedObject);
        $this->reflectionProperties      = $this->generateReflectionProperties($this->hydratedObject);
        $this->hydrationData             = $this->generateHydrationData($this->hydratedObject);
        $this->objectPropertyHydrator    = new ObjectProperty();
        $this->classMethodsHydrator      = new ClassMethods(false);
    }

    /**
     * @iterations 20000
     * @baseline
     * @group hydration
     */
    public function reflectionHydrate()
    {
        foreach ($this->reflectionProperties as $name => $property) {
            $property->setValue($this->hydratedObject, $name);
        }
    }

    /**
     * @iterations 20000
     * @group hydration
     */
    public function generatedHydratorHydrate()
    {
        $this->hydrator->hydrate($this->hydrationData, $this->hydratedObject);
    }

    /**
     * @iterations 20000
     * @group hydration
     */
    public function classMethodsHydrate()
    {
        $data = $this->classMethodsHydrator->hydrate($this->hydrationData, $this->hydratedObject);
    }

    /**
     * @iterations 20000
     * @baseline
     * @group extraction
     */
    public function reflectionExtract()
    {
        $data = [];

        foreach ($this->reflectionProperties as $name => $property) {
            $data[$name] = $property->getValue($this->hydratedObject);
        }
    }

    /**
     * @iterations 20000
     * @group extraction
     */
    public function generatedHydratorExtract()
    {
        $data = $this->hydrator->extract($this->hydratedObject);
    }

    /**
     * @iterations 20000
     * @group extraction
     */
    public function objectPropertyExtract()
    {
        $data = $this->objectPropertyHydrator->extract($this->hydratedObject);
    }

    /**
     * @iterations 20000
     * @group extraction
     */
    public function classMethodsExtract()
    {
        $data = $this->classMethodsHydrator->extract($this->hydratedObject);
    }

    /**
     * Generates a hydrator for the given class name, and retrieves an instance of it
     *
     * @param object $object
     *
     * @return \Zend\Stdlib\Hydrator\HydratorInterface
     */
    private function generateHydrator($object)
    {
        $config = new Configuration(get_class($object));

        $config->setGeneratorStrategy(new EvaluatingGeneratorStrategy());

        $generatedClass = $config->createFactory()->getHydratorClass();

        return new $generatedClass();
    }

    /**
     * @param object $object
     *
     * @return \ReflectionProperty[]
     */
    private function generateReflectionProperties($object)
    {
        $reflection = new ReflectionClass($object);
        $properties = [];

        foreach ($reflection->getProperties() as $reflectionProperty) {
            $reflectionProperty->setAccessible(true);

            $properties[$reflectionProperty->getName()] = $reflectionProperty;
        }

        return $properties;
    }

    /**
     * @param object $object
     *
     * @return mixed[]
     */
    private function generateHydrationData($object)
    {
        $reflection = new ReflectionClass($object);
        $data       = [];

        foreach ($reflection->getProperties() as $reflectionProperty) {
            $reflectionProperty->setAccessible(true);

            $name        = $reflectionProperty->getName();
            $data[$name] = $name;
        }

        return $data;
    }
}
