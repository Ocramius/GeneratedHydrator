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

declare(strict_types=1);

namespace GeneratedHydratorTest\ClassGenerator\Hydrator\PropertyGenerator;

use GeneratedHydrator\ClassGenerator\Hydrator\PropertyGenerator\PropertyAccessor;
use PHPUnit_Framework_TestCase;
use ReflectionProperty;

/**
 * Tests for {@see \GeneratedHydrator\ClassGenerator\Hydrator\PropertyGenerator\PropertyAccessor}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 *
 * @covers \GeneratedHydrator\ClassGenerator\Hydrator\PropertyGenerator\PropertyAccessor
 */
class PropertyAccessorTest extends PHPUnit_Framework_TestCase
{
    /**
     * {@inheritDoc}
     *
     * @covers \GeneratedHydrator\ClassGenerator\Hydrator\PropertyGenerator\PropertyAccessor::__construct
     */
    protected function createProperty()
    {
        $property = new ReflectionProperty('GeneratedHydratorTestAsset\\BaseClass', 'publicProperty');

        $accessor1 = new PropertyAccessor($property, 'foo');
        $accessor2 = new PropertyAccessor($property, 'foo');

        $this->assertNotSame($accessor1, $accessor2);
    }

    /**
     * @covers \GeneratedHydrator\ClassGenerator\Hydrator\PropertyGenerator\PropertyAccessor::__construct
     * @covers \GeneratedHydrator\ClassGenerator\Hydrator\PropertyGenerator\PropertyAccessor::getOriginalProperty
     */
    public function testGetOriginalProperty()
    {
        $property = new ReflectionProperty('GeneratedHydratorTestAsset\\BaseClass', 'publicProperty');

        $accessor = new PropertyAccessor($property, 'foo');

        $this->assertSame($property, $accessor->getOriginalProperty());
    }

    /**
     * @covers \GeneratedHydrator\ClassGenerator\Hydrator\PropertyGenerator\PropertyAccessor::__construct
     */
    public function testHasCorrectName()
    {
        $property = new ReflectionProperty('GeneratedHydratorTestAsset\\BaseClass', 'publicProperty');

        $accessor = new PropertyAccessor($property, 'Foo');

        $this->assertStringMatchesFormat('publicPropertyFoo%s', $accessor->props[0]->name);
    }
}
