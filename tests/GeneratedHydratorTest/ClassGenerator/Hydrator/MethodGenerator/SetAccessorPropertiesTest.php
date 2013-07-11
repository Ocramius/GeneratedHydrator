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

namespace GeneratedHydratorTest\ClassGenerator\Hydrator\MethodGenerator;

use PHPUnit_Framework_TestCase;
use GeneratedHydrator\ClassGenerator\Hydrator\MethodGenerator\SetAccessorProperties;
use GeneratedHydrator\ClassGenerator\Hydrator\PropertyGenerator\PropertyAccessor;
use ReflectionProperty;

/**
 * Tests for {@see \GeneratedHydrator\ClassGenerator\Hydrator\MethodGenerator\SetAccessorProperties}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 *
 * @covers \GeneratedHydrator\ClassGenerator\Hydrator\MethodGenerator\SetAccessorProperties
 */
class SetAccessorPropertiesTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers \GeneratedHydrator\ClassGenerator\Hydrator\MethodGenerator\SetAccessorProperties::__construct
     */
    public function testGeneratedStructure()
    {
        $property = new ReflectionProperty('GeneratedHydratorTestAsset\\BaseClass', 'publicProperty');
        $accessor = $this
            ->getMockBuilder('GeneratedHydrator\\ClassGenerator\\Hydrator\\PropertyGenerator\\PropertyAccessor')
            ->disableOriginalConstructor()
            ->getMock();

        $accessor->expects($this->any())->method('getName')->will($this->returnValue('foo'));
        $accessor->expects($this->any())->method('getOriginalProperty')->will($this->returnValue($property));

        $setAccessors = new SetAccessorProperties(array($accessor));
        $parameters   = $setAccessors->getParameters();

        $this->assertSame("\$this->foo = \$accessorProperties['publicProperty'];\n", $setAccessors->getBody());
        $this->assertSame('setAccessorProperties', $setAccessors->getName());
        $this->assertCount(1, $parameters);

        /* @var $parameter \Zend\Code\Generator\ParameterGenerator */
        $parameter = reset($parameters);

        $this->assertSame('accessorProperties', $parameter->getName());
        $this->assertSame('array', $parameter->getType());
    }
}
