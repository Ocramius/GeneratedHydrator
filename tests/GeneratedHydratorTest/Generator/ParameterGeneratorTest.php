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

namespace GeneratedHydratorTest\Generator;

use PHPUnit_Framework_TestCase;
use GeneratedHydrator\Generator\ParameterGenerator;
use Zend\Code\Reflection\ParameterReflection;

/**
 * Tests for {@see \GeneratedHydrator\Generator\ParameterGenerator}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
class ParameterGeneratorTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers \GeneratedHydrator\Generator\ParameterGenerator::generate
     */
    public function testGeneratesProperTypeHint()
    {
        $generator = new ParameterGenerator('foo');

        $generator->setType('array');
        $this->assertSame('array $foo', $generator->generate());

        $generator->setType('stdClass');
        $this->assertSame('\\stdClass $foo', $generator->generate());

        $generator->setType('\\fooClass');
        $this->assertSame('\\fooClass $foo', $generator->generate());
    }

    /**
     * @covers \GeneratedHydrator\Generator\ParameterGenerator::generate
     */
    public function testGeneratesMethodWithCallableType()
    {
        if (PHP_VERSION_ID < 50400) {
            $this->markTestSkipped('`callable` is only supported in PHP >=5.4.0');
        }

        $generator = new ParameterGenerator();

        $generator->setType('callable');
        $generator->setName('foo');

        $this->assertSame('callable $foo', $generator->generate());
    }

    /**
     * @covers \GeneratedHydrator\Generator\ParameterGenerator::fromReflection
     */
    public function testVisitMethodWithCallable()
    {
        if (PHP_VERSION_ID < 50400) {
            $this->markTestSkipped('`callable` is only supported in PHP >=5.4.0');
        }

        $parameter = new ParameterReflection(
            array('GeneratedHydratorTestAsset\\CallableTypeHintClass', 'callableTypeHintMethod'),
            'parameter'
        );

        $generator = ParameterGenerator::fromReflection($parameter);

        $this->assertSame('callable', $generator->getType());
    }
}
