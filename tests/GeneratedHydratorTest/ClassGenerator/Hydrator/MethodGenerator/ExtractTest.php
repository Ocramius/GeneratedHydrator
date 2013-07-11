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
use GeneratedHydrator\ClassGenerator\Hydrator\MethodGenerator\Extract;
use GeneratedHydrator\ClassGenerator\Hydrator\PropertyGenerator\PropertyAccessor;
use ReflectionProperty;

/**
 * Tests for {@see \GeneratedHydrator\ClassGenerator\Hydrator\MethodGenerator\Extract}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 *
 * @covers \GeneratedHydrator\ClassGenerator\Hydrator\MethodGenerator\Extract
 */
class ExtractTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers \GeneratedHydrator\ClassGenerator\Hydrator\MethodGenerator\Extract::__construct
     */
    public function testSignature()
    {
        $extract    = new Extract(array(), array());
        $parameters = $extract->getParameters();

        $this->assertSame('extract', $extract->getName());
        $this->assertCount(1, $parameters);

        /* @var $objectParam \Zend\Code\Generator\ParameterGenerator */
        $objectParam = reset($parameters);

        $this->assertSame('object', $objectParam->getName());
        $this->assertNull($objectParam->getType());
    }

    /**
     * @covers \GeneratedHydrator\ClassGenerator\Hydrator\MethodGenerator\Extract::__construct
     */
    public function testGeneratedStructureWithMixedAccessType()
    {
        $publicProperty    = new ReflectionProperty('GeneratedHydratorTestAsset\\BaseClass', 'publicProperty');
        $protectedProperty = new ReflectionProperty('GeneratedHydratorTestAsset\\BaseClass', 'protectedProperty');
        $property          = new ReflectionProperty('GeneratedHydratorTestAsset\\BaseClass', 'privateProperty');
        $accessor          = $this
            ->getMockBuilder('GeneratedHydrator\\ClassGenerator\\Hydrator\\PropertyGenerator\\PropertyAccessor')
            ->disableOriginalConstructor()
            ->getMock();

        $accessor->expects($this->any())->method('getName')->will($this->returnValue('foo'));
        $accessor->expects($this->any())->method('getOriginalProperty')->will($this->returnValue($property));

        $extract = new Extract(array($publicProperty, $protectedProperty), array($accessor));

        $this->assertSame(
            "\$data = (array) \$object;\n\n"
            . "return array(\n"
            . "    'publicProperty' => \$object->publicProperty,\n"
            . "    'protectedProperty' => \$data[\"\\0*\\0protectedProperty\"],\n"
            . "    'privateProperty' => \$data[\"\\0GeneratedHydratorTestAsset\\BaseClass\\0privateProperty\"],\n"
            . ");",
            $extract->getBody()
        );
    }

    /**
     * @covers \GeneratedHydrator\ClassGenerator\Hydrator\MethodGenerator\Extract::__construct
     */
    public function testGeneratedStructureWithPublicProperties()
    {
        $publicProperty = new ReflectionProperty('GeneratedHydratorTestAsset\\BaseClass', 'publicProperty');
        $extract        = new Extract(array($publicProperty), array());

        $this->assertSame(
            "return array(\n"
            . "    'publicProperty' => \$object->publicProperty,\n"
            . ");",
            $extract->getBody()
        );
    }

    /**
     * @covers \GeneratedHydrator\ClassGenerator\Hydrator\MethodGenerator\Extract::__construct
     */
    public function testGeneratedStructureWithPublicAndProtectedProperties()
    {
        $publicProperty = new ReflectionProperty('GeneratedHydratorTestAsset\\BaseClass', 'publicProperty');
        $protectedProperty = new ReflectionProperty('GeneratedHydratorTestAsset\\BaseClass', 'protectedProperty');
        $extract        = new Extract(array($publicProperty, $protectedProperty), array());

        $this->assertSame(
            "return array(\n"
            . "    'publicProperty' => \$object->publicProperty,\n"
            . "    'protectedProperty' => \$object->protectedProperty,\n"
            . ");",
            $extract->getBody()
        );
    }
}
