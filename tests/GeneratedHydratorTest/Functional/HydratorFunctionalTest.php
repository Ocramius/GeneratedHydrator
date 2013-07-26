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
use GeneratedHydrator\Factory\HydratorFactory;
use GeneratedHydratorTestAsset\BaseClass;
use GeneratedHydratorTestAsset\ClassWithMixedProperties;
use GeneratedHydratorTestAsset\ClassWithPrivateProperties;
use GeneratedHydratorTestAsset\ClassWithProtectedProperties;
use GeneratedHydratorTestAsset\ClassWithPublicProperties;
use GeneratedHydratorTestAsset\EmptyClass;
use GeneratedHydratorTestAsset\HydratedObject;
use PHPUnit_Framework_TestCase;
use ReflectionClass;
use stdClass;

/**
 * Tests for {@see \GeneratedHydrator\ProxyGenerator\HydratedObject} produced objects
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 *
 * @group Functional
 */
class HydratorFunctionalTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider getProxyClasses
     *
     * @param object $instance
     */
    public function testHydrator($instance)
    {
        $reflection  = new ReflectionClass($instance);
        $properties  = $reflection->getProperties();
        $initialData = array();
        $newData     = array();

        foreach ($properties as $property) {
            $propertyName = $property->getName();

            $property->setAccessible(true);
            $initialData[$propertyName] = $property->getValue($instance);
            $newData[$propertyName]     = $property->getName() . '__new__value';
        }

        $proxy = $this->generateProxy($instance);

        $this->assertSame($initialData, $proxy->extract($instance));
        $this->assertSame($instance, $proxy->hydrate($newData, $instance));

        $inspectionData = array();

        foreach ($properties as $property) {
            $propertyName = $property->getName();

            $property->setAccessible(true);
            $inspectionData[$propertyName] = $property->getValue($instance);
        }

        $this->assertSame($inspectionData, $newData);
        $this->assertSame($inspectionData, $proxy->extract($instance));
    }

    public function testDisabledMethod()
    {
        $this->markTestIncomplete('Methods have to be disabled - currently only removing them');

        $proxy = $this->generateProxy(new HydratedObject());

        $this->setExpectedException('GeneratedHydrator\Exception\DisabledMethodException');
        $proxy->doFoo();
    }

    /**
     * @return array
     */
    public function getProxyClasses()
    {
        return array(
            array(new stdClass()),
            array(new EmptyClass()),
            array(new HydratedObject()),
            array(new BaseClass()),
            array(new ClassWithPublicProperties()),
            array(new ClassWithProtectedProperties()),
            array(new ClassWithPrivateProperties()),
            array(new ClassWithMixedProperties()),
        );
    }

    /**
     * Generates a proxy for the given class name, and retrieves its class name
     *
     * @param object $instance
     *
     * @return \GeneratedHydratorTestAsset\HydratedObject|\Zend\Stdlib\Hydrator\HydratorInterface
     */
    private function generateProxy($instance)
    {
        $parentClassName    = get_class($instance);
        $generatedClassName = __NAMESPACE__ . '\\' . UniqueIdentifierGenerator::getIdentifier('Foo');
        $config             = new Configuration($parentClassName);
        $inflector          = $this->getMock('CodeGenerationUtils\\Inflector\\ClassNameInflectorInterface');

        $inflector
            ->expects($this->any())
            ->method('getProxyClassName')
            ->with($parentClassName)
            ->will($this->returnValue($generatedClassName));
        $inflector
            ->expects($this->any())
            ->method('getUserClassName')
            ->with($parentClassName)
            ->will($this->returnValue($parentClassName));

        $config->setClassNameInflector($inflector);
        $config->setGeneratorStrategy(new EvaluatingGeneratorStrategy());

        $factory = new HydratorFactory($config);
        $proxy   = $factory->getProxyClass($parentClassName);

        return new $proxy;
    }
}
