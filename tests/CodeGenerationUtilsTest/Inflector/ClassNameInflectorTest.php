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

namespace CodeGenerationUtilsTest\Inflector;

use PHPUnit_Framework_TestCase;
use CodeGenerationUtils\Inflector\ClassNameInflector;
use CodeGenerationUtils\Inflector\ClassNameInflectorInterface;

/**
 * Tests for {@see \CodeGenerationUtils\Inflector\ClassNameInflector}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
class ClassNameInflectorTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider getClassNames
     *
     * @covers \CodeGenerationUtils\Inflector\ClassNameInflector::__construct
     * @covers \CodeGenerationUtils\Inflector\ClassNameInflector::getUserClassName
     * @covers \CodeGenerationUtils\Inflector\ClassNameInflector::getGeneratedClassName
     * @covers \CodeGenerationUtils\Inflector\ClassNameInflector::isGeneratedClassName
     */
    public function testInflector($realClassName, $proxyClassName)
    {
        $inflector = new ClassNameInflector('ProxyNS');

        $this->assertFalse($inflector->isGeneratedClassName($realClassName));
        $this->assertTrue($inflector->isGeneratedClassName($proxyClassName));
        $this->assertStringMatchesFormat($realClassName, $inflector->getUserClassName($realClassName));
        $this->assertStringMatchesFormat($proxyClassName, $inflector->getGeneratedClassName($proxyClassName));
        $this->assertStringMatchesFormat($proxyClassName, $inflector->getGeneratedClassName($realClassName));
        $this->assertStringMatchesFormat($realClassName, $inflector->getUserClassName($proxyClassName));
    }

    /**
     * @covers \CodeGenerationUtils\Inflector\ClassNameInflector::getGeneratedClassName
     */
    public function testGeneratesSameClassNameWithSameParameters()
    {
        $inflector = new ClassNameInflector('ProxyNS');

        $this->assertSame($inflector->getGeneratedClassName('Foo\\Bar'), $inflector->getGeneratedClassName('Foo\\Bar'));
        $this->assertSame(
            $inflector->getGeneratedClassName('Foo\\Bar', array('baz' => 'tab')),
            $inflector->getGeneratedClassName('Foo\\Bar', array('baz' => 'tab'))
        );
        $this->assertSame(
            $inflector->getGeneratedClassName('Foo\\Bar', array('tab' => 'baz')),
            $inflector->getGeneratedClassName('Foo\\Bar', array('tab' => 'baz'))
        );
    }

    /**
     * @covers \CodeGenerationUtils\Inflector\ClassNameInflector::getGeneratedClassName
     */
    public function testGeneratesDifferentClassNameWithDifferentParameters()
    {
        $inflector = new ClassNameInflector('ProxyNS');

        $this->assertNotSame(
            $inflector->getGeneratedClassName('Foo\\Bar'),
            $inflector->getGeneratedClassName('Foo\\Bar', array('foo' => 'bar'))
        );
        $this->assertNotSame(
            $inflector->getGeneratedClassName('Foo\\Bar', array('baz' => 'tab')),
            $inflector->getGeneratedClassName('Foo\\Bar', array('tab' => 'baz'))
        );
        $this->assertNotSame(
            $inflector->getGeneratedClassName('Foo\\Bar', array('foo' => 'bar', 'tab' => 'baz')),
            $inflector->getGeneratedClassName('Foo\\Bar', array('foo' => 'bar'))
        );
        $this->assertNotSame(
            $inflector->getGeneratedClassName('Foo\\Bar', array('foo' => 'bar', 'tab' => 'baz')),
            $inflector->getGeneratedClassName('Foo\\Bar', array('tab' => 'baz', 'foo' => 'bar'))
        );
    }

    /**
     * @return array
     */
    public function getClassNames()
    {
        return array(
            array('Foo', 'ProxyNS\\' . ClassNameInflectorInterface::PROXY_MARKER . '\\Foo\\%s'),
            array('Foo\\Bar', 'ProxyNS\\' . ClassNameInflectorInterface::PROXY_MARKER . '\\Foo\\Bar\\%s'),
        );
    }
}
