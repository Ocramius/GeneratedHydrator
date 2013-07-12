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
use GeneratedHydrator\ClassGenerator\Hydrator\MethodGenerator\Constructor;
use GeneratedHydrator\ClassGenerator\Hydrator\PropertyGenerator\PropertyAccessor;
use ReflectionClass;
use ReflectionProperty;

/**
 * Tests for {@see \GeneratedHydrator\ClassGenerator\Hydrator\MethodGenerator\Constructor}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 *
 * @covers \GeneratedHydrator\ClassGenerator\Hydrator\MethodGenerator\Constructor
 */
class ConstructorTest extends PHPUnit_Framework_TestCase
{
    public function testGeneratedStructure()
    {
        $property = new ReflectionProperty('GeneratedHydratorTestAsset\\BaseClass', 'publicProperty');
        $accessor = $this
            ->getMockBuilder('GeneratedHydrator\\ClassGenerator\\Hydrator\\PropertyGenerator\\PropertyAccessor')
            ->disableOriginalConstructor()
            ->getMock();

        $accessor->expects($this->any())->method('getName')->will($this->returnValue('foo'));
        $accessor->expects($this->any())->method('getOriginalProperty')->will($this->returnValue($property));

        $constructor = new Constructor(new ReflectionClass(__CLASS__), array($accessor));

        $this->assertSame('__construct', $constructor->getName());
        $this->assertSame(
            "\$reflectionClass = new \\ReflectionClass(" . var_export(__CLASS__, true) . ");\n\n"
            . "\$this->foo = \$reflectionClass->getProperty('publicProperty');\n\n"
            . "\$this->foo->setAccessible(true);\n",
            $constructor->getBody()
        );
        $this->assertEmpty($constructor->getParameters());
    }

    public function testGeneratedStructureWithoutAccessors()
    {
        $constructor = new Constructor(new ReflectionClass(__CLASS__), array());

        $this->assertEmpty($constructor->getBody());
        $this->assertEmpty($constructor->getParameters());
    }
}
