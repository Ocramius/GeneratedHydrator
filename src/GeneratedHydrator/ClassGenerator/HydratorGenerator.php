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

namespace GeneratedHydrator\ClassGenerator;

use CodeGenerationUtils\Visitor\ClassClonerVisitor;
use GeneratedHydrator\ClassGenerator\Hydrator\MethodGenerator\Constructor;
use GeneratedHydrator\ClassGenerator\Hydrator\MethodGenerator\DisabledMagicMethod;
use GeneratedHydrator\ClassGenerator\Hydrator\MethodGenerator\DisabledMethod;
use GeneratedHydrator\ClassGenerator\Hydrator\MethodGenerator\Extract;
use GeneratedHydrator\ClassGenerator\Hydrator\MethodGenerator\Hydrate;
use GeneratedHydrator\ClassGenerator\Hydrator\PropertyGenerator\PropertyAccessor;
use GeneratedHydrator\CodeGenerator\Visitor\HydratorMethodsVisitor;
use ProxyManager\Generator\MethodGenerator;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;
use Zend\Code\Generator\ClassGenerator;
use Zend\Code\Reflection\MethodReflection;

/**
 * Generator for proxies being a hydrator - {@see \Zend\Stdlib\Hydrator\HydratorInterface}
 * for objects
 *
 * {@inheritDoc}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
class HydratorGenerator
{
    /**
     * {@inheritDoc}
     */
    public function generate(ReflectionClass $originalClass)
    {
        $cloner = new HydratorMethodsVisitor($originalClass);

        $traverser = new \PHPParser_NodeTraverser();

        $traverser->addVisitor($cloner);

        die(var_dump($traverser->traverse(array())));

        $interfaces = array('Zend\\Stdlib\\Hydrator\\HydratorInterface');

        if ($originalClass->isInterface()) {
            $interfaces[] = $originalClass->getName();
        } else {
            $classGenerator->setExtendedClass($originalClass->getName());
        }

        $classGenerator->setImplementedInterfaces($interfaces);

        $excluded = array(
            '__get'    => true,
            '__set'    => true,
            '__isset'  => true,
            '__unset'  => true,
            '__clone'  => true,
            '__sleep'  => true,
            '__wakeup' => true,
        );

        /* @var $methods ReflectionMethod[] */
        $methods = array_filter(
            $originalClass->getMethods(ReflectionMethod::IS_PUBLIC | ReflectionMethod::IS_PROTECTED),
            function (ReflectionMethod $method) use ($excluded) {
                return ! (
                    $method->isConstructor()
                    || isset($excluded[strtolower($method->getName())])
                    || $method->isFinal()
                    || $method->isStatic()
                );
            }
        );

        foreach ($methods as $method) {
            $classGenerator->addMethodFromGenerator(
                DisabledMethod::fromReflection(
                    new MethodReflection($method->getDeclaringClass()->getName(), $method->getName())
                )
            );
        }

        foreach (array('__clone', '__sleep', '__wakeup') as $magicMethod) {
            $classGenerator->addMethodFromGenerator(new DisabledMagicMethod($originalClass, $magicMethod));
        }

        $classGenerator->addMethodFromGenerator(new DisabledMagicMethod($originalClass, '__get', array('name')));
        $classGenerator->addMethodFromGenerator(
            new DisabledMagicMethod($originalClass, '__set', array('name', 'value'))
        );
        $classGenerator->addMethodFromGenerator(new DisabledMagicMethod($originalClass, '__isset', array('name')));
        $classGenerator->addMethodFromGenerator(new DisabledMagicMethod($originalClass, '__unset', array('name')));

        $accessibleFlag       = ReflectionProperty::IS_PUBLIC | ReflectionProperty::IS_PROTECTED;
        $accessibleProperties = $originalClass->getProperties($accessibleFlag);
        $inaccessibleProps    = $originalClass->getProperties(ReflectionProperty::IS_PRIVATE);
        $propertyWriters      = array();

        foreach ($inaccessibleProps as $inaccessibleProp) {
            $propertyWriters[$inaccessibleProp->getName()] = new PropertyAccessor($inaccessibleProp, 'Writer');
        }

        $classGenerator->addProperties($propertyWriters);

        $classGenerator->addMethodFromGenerator(new Constructor($originalClass, $propertyWriters));
        $classGenerator->addMethodFromGenerator(new Hydrate($accessibleProperties, $propertyWriters));
        $classGenerator->addMethodFromGenerator(new Extract($accessibleProperties, $propertyWriters));
    }
}
