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
use GeneratedHydratorTestAsset\ClassWithStaticProperties;
use GeneratedHydratorTestAsset\EmptyClass;
use GeneratedHydratorTestAsset\HydratedObject;
use PHPUnit_Framework_TestCase;
use ReflectionClass;
use stdClass;

/**
 * Tests for {@see \GeneratedHydrator\ClassGenerator\HydratorGenerator} produced objects
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 *
 * @group Functional
 */
class HydratorFunctionalTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider getHydratorClasses
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
            if ($property->isStatic()) {
                continue;
            }

            $propertyName = $property->getName();

            $property->setAccessible(true);
            $initialData[$propertyName] = $property->getValue($instance);
            $newData[$propertyName]     = $property->getName() . '__new__value';
        }

        $generatedClass = $this->generateHydrator($instance);

        $this->assertSame($initialData, $generatedClass->extract($instance));
        $this->assertSame($instance, $generatedClass->hydrate($newData, $instance));

        $inspectionData = array();

        foreach ($properties as $property) {
            if ($property->isStatic()) {
                continue;
            }

            $propertyName = $property->getName();

            $property->setAccessible(true);
            $inspectionData[$propertyName] = $property->getValue($instance);
        }

        $this->assertSame($inspectionData, $newData);
        $this->assertSame($inspectionData, $generatedClass->extract($instance));
    }

    public function testDisabledMethod()
    {
        $this->markTestIncomplete('Methods have to be disabled - currently only removing them');

        $generatedClass = $this->generateHydrator(new HydratedObject());

        $this->setExpectedException('GeneratedHydrator\Exception\DisabledMethodException');
        $generatedClass->doFoo();
    }

    /**
     * @return array
     */
    public function getHydratorClasses()
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
            array(new ClassWithStaticProperties()),
        );
    }

    /**
     * Generates a hydrator for the given class name, and retrieves its class name
     *
     * @param object $instance
     *
     * @return \GeneratedHydratorTestAsset\HydratedObject|\Zend\Stdlib\Hydrator\HydratorInterface
     */
    private function generateHydrator($instance)
    {
        $parentClassName    = get_class($instance);
        $generatedClassName = __NAMESPACE__ . '\\' . UniqueIdentifierGenerator::getIdentifier('Foo');
        $config             = new Configuration($parentClassName);
        $inflector          = $this->getMock('CodeGenerationUtils\\Inflector\\ClassNameInflectorInterface');

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

        $generatedClass = $config->createFactory()->getHydratorClass();

        return new $generatedClass;
    }
}
