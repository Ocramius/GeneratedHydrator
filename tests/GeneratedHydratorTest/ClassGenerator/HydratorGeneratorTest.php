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

use CodeGenerationUtils\Visitor\ClassRenamerVisitor;
use GeneratedHydrator\ClassGenerator\HydratorGenerator;
use CodeGenerationUtils\Inflector\Util\UniqueIdentifierGenerator;
use CodeGenerationUtils\GeneratorStrategy\EvaluatingGeneratorStrategy;
use PhpParser\NodeTraverser;
use PHPUnit_Framework_TestCase;
use ReflectionClass;

/**
 * Tests for {@see \GeneratedHydrator\ClassGenerator\HydratorGenerator}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 *
 * @covers \GeneratedHydrator\ClassGenerator\HydratorGenerator
 */
class HydratorGeneratorTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider getTestedImplementations
     *
     * Verifies that generated code is valid and implements expected interfaces
     */
    public function testGeneratesValidCode($className)
    {
        $generator          = new HydratorGenerator();
        $generatedClassName = UniqueIdentifierGenerator::getIdentifier('HydratorGeneratorTest');
        $originalClass      = new ReflectionClass($className);
        $generatorStrategy  = new EvaluatingGeneratorStrategy();
        $traverser          = new NodeTraverser();

        $traverser->addVisitor(new ClassRenamerVisitor($originalClass, $generatedClassName));
        $generatorStrategy->generate($traverser->traverse($generator->generate($originalClass)));

        $generatedReflection = new ReflectionClass($generatedClassName);

        if ($originalClass->isInterface()) {
            $this->assertTrue($generatedReflection->implementsInterface($className));
        } else {
            $this->assertInstanceOf('ReflectionClass', $generatedReflection->getParentClass());
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
        );
    }

    /**
     * {@inheritDoc}
     */
    protected function getExpectedImplementedInterfaces()
    {
        return array('Zend\\Stdlib\\Hydrator\\HydratorInterface');
    }
}
