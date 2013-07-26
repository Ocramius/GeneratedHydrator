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

namespace GeneratedHydratorTest\Functional;

use CodeGenerationUtils\GeneratorStrategy\EvaluatingGeneratorStrategy;
use CodeGenerationUtils\Inflector\Util\UniqueIdentifierGenerator;
use GeneratedHydrator\Configuration;
use GeneratedHydratorTestAsset\BaseClass;
use GeneratedHydratorTestAsset\ClassWithMixedProperties;
use GeneratedHydratorTestAsset\ClassWithPrivateProperties;
use GeneratedHydratorTestAsset\ClassWithProtectedProperties;
use GeneratedHydratorTestAsset\ClassWithPublicProperties;
use GeneratedHydratorTestAsset\HydratedObject;
use ReflectionClass;
use ReflectionProperty;
use stdClass;
use Zend\Stdlib\Hydrator\HydratorInterface;

/**
 * Tests for {@see \GeneratedHydrator\ClassGenerator\HydratorGenerator} produced objects
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 *
 * @group Performance
 */
class HydratorPerformanceTest extends BasePerformanceTest
{
    /**
     * @dataProvider getTestedClasses
     *
     * @param object                                $instance
     * @param \Zend\Stdlib\Hydrator\HydratorInterface $hydrator
     * @param \ReflectionProperty[]                 $properties
     * @param array                                 $data
     */
    public function testHydrationPerformance($instance, HydratorInterface $hydrator, array $properties, array $data)
    {
        $iterations = 20000;
        $className  = get_class($instance);

        $this->startCapturing();

        for ($i = 0; $i < $iterations; $i += 1) {
            foreach ($properties as $key => $property) {
                $property->setValue($instance, $data[$key]);
            }
        }

        $base = $this->endCapturing('Baseline hydration: ' . $iterations . ' "' . $className . '": %fms / %fKb');
        $this->startCapturing();

        for ($i = 0; $i < $iterations; $i += 1) {
            $hydrator->hydrate($data, $instance);
        }

        $proxy = $this->endCapturing('Generated hydration: ' . $iterations . ' "' . $className . '": %fms / %fKb');

        $this->compareProfile($base, $proxy);
    }

    /**
     * @dataProvider getTestedClasses
     *
     * @param object                                $instance
     * @param \Zend\Stdlib\Hydrator\HydratorInterface $hydrator
     * @param \ReflectionProperty[]                 $properties
     */
    public function testExtractionPerformance($instance, HydratorInterface $hydrator, array $properties)
    {
        $iterations = 20000;
        $className  = get_class($instance);

        $this->startCapturing();

        for ($i = 0; $i < $iterations; $i += 1) {
            foreach ($properties as $property) {
                $property->getValue($instance);
            }
        }

        $base = $this->endCapturing('Baseline extraction: ' . $iterations . ' "' . $className . '": %fms / %fKb');
        $this->startCapturing();

        for ($i = 0; $i < $iterations; $i += 1) {
            $hydrator->extract($instance);
        }

        $proxy = $this->endCapturing('Generated extraction: ' . $iterations . ' "' . $className . '": %fms / %fKb');

        $this->compareProfile($base, $proxy);
    }

    /**
     * @return array
     */
    public function getTestedClasses()
    {
        $data = array();

        $classes = array(
            new stdClass(),
            new BaseClass(),
            new HydratedObject(),
            new ClassWithPrivateProperties(),
            new ClassWithProtectedProperties(),
            new ClassWithPublicProperties(),
            new ClassWithMixedProperties(),
        );

        foreach ($classes as $instance) {
            $definitions = $this->generateHydrator($instance);
            $hydrator    = $definitions['hydrator'];
            $properties  = $definitions['properties'];
            $values      = array();

            foreach (array_keys($properties) as $name) {
                $values[$name] = $name;
            }

            $data[] = array($instance, $hydrator, $properties, $values);
        }

        return $data;
    }

    /**
     * Generates a proxy for the given class name, and retrieves an instance of it
     *
     * @param object $object
     *
     * @return array
     */
    private function generateHydrator($object)
    {
        $parentClassName    = get_class($object);
        $generatedClassName = __NAMESPACE__ . '\\' . UniqueIdentifierGenerator::getIdentifier('Foo');
        $config             = new Configuration($parentClassName);
        $inflector          = $this->getMock('CodeGenerationUtils\\Inflector\\ClassNameInflectorInterface');
        $reflection         = new ReflectionClass($object);
        $properties         = array();
        $accessors          = array();

        $inflector
            ->expects($this->any())
            ->method('getGeneratedClassName')
            ->with($parentClassName)
            ->will($this->returnValue($generatedClassName));
        $inflector
            ->expects($this->any())
            ->method('getUserClassName')
            ->with($parentClassName)
            ->will($this->returnValue($parentClassName));

        $config->setClassNameInflector($inflector);
        $config->setGeneratorStrategy(new EvaluatingGeneratorStrategy());

        foreach ($reflection->getProperties() as $reflectionProperty) {
            $reflectionProperty->setAccessible(true);

            $properties[$reflectionProperty->getName()] = $reflectionProperty;

            if ($reflectionProperty->isPrivate()) {
                $accessors[$reflectionProperty->getName()] = $reflectionProperty;
            }
        }

        $generatedClass = $config->createFactory()->getHydratorClass();

        return array(
            'hydrator'   => new $generatedClass,
            'properties' => $properties,
        );
    }
}
