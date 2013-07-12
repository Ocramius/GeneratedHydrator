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

namespace GeneratedHydratorTest\ClassGenerator;

use GeneratedHydrator\ClassGenerator\HydratorGenerator;
use GeneratedHydrator\ClassGenerator\PhpParserClassGenerator;
use ProxyManager\Generator\ClassGenerator;
use ProxyManager\Generator\Util\UniqueIdentifierGenerator;
use ProxyManager\GeneratorStrategy\EvaluatingGeneratorStrategy;
use ReflectionClass;

/**
 * Tests for {@see \GeneratedHydrator\ClassGenerator\HydratorGenerator}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 *
 * @covers \GeneratedHydrator\ClassGenerator\HydratorGenerator
 */
class HydratorGeneratorTest extends AbstractClassGeneratorTest
{
    /**
     * @dataProvider getTestedImplementations
     *
     * Verifies that generated code is valid and implements expected interfaces
     */
    public function testGeneratesValidCode($className)
    {
        $generator          = new HydratorGenerator();
        $generatedClassName = UniqueIdentifierGenerator::getIdentifier('AbstractProxyGeneratorTest');
        $generatedClass     = new PhpParserClassGenerator($generatedClassName);
        $originalClass      = new ReflectionClass($className);
        $generatorStrategy  = new EvaluatingGeneratorStrategy();

        $generator->generate($originalClass, $generatedClass);
        $generatorStrategy->generate($generatedClass);

        $generatedReflection = new ReflectionClass($generatedClassName);

        if ($originalClass->isInterface()) {
            $this->assertTrue($generatedReflection->implementsInterface($className));
        } else {
            $this->assertSame($originalClass->getName(), $generatedReflection->getParentClass()->getName());
        }

        $this->assertSame($generatedClassName, $generatedReflection->getName());

        foreach ($this->getExpectedImplementedInterfaces() as $interface) {
            $this->assertTrue($generatedReflection->implementsInterface($interface));
        }
    }

    /**
     * @return array
     */
    public function getTestedImplementations()
    {
        return array(
            array('GeneratedHydratorTestAsset\\BaseClass'),
            array('GeneratedHydratorTestAsset\\ClassWithMagicMethods'),
            array('GeneratedHydratorTestAsset\\ClassWithByRefMagicMethods'),
            array('GeneratedHydratorTestAsset\\ClassWithMixedProperties'),
            array('GeneratedHydratorTestAsset\\BaseInterface'),
        );
    }

    /**
     * {@inheritDoc}
     */
    protected function getExpectedImplementedInterfaces()
    {
        return array(
            'ProxyManager\\Proxy\\ProxyInterface',
            'Zend\\Stdlib\\Hydrator\\HydratorInterface'
        );
    }
}
