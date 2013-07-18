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

use CodeGenerationUtils\ReflectionBuilder\ClassBuilder;
use CodeGenerationUtils\Visitor\MethodDisablerVisitor;
use GeneratedHydrator\ClassGenerator\Hydrator\PropertyGenerator\PropertyAccessor;
use GeneratedHydrator\CodeGenerator\Visitor\HydratorMethodsVisitor;
use PHPParser_NodeTraverser;
use ProxyManager\Generator\MethodGenerator;
use ReflectionClass;

/**
 * Generator for proxies being a hydrator - {@see \Zend\Stdlib\Hydrator\HydratorInterface}
 * for objects
 *
 * {@inheritDoc}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
class HydratorGenerator implements ClassGeneratorInterface
{
    /**
     * {@inheritDoc}
     */
    public function generate(ReflectionClass $originalClass)
    {
        $builder   = new ClassBuilder();

        $ast = $builder->fromReflection($originalClass);

        // step 1: remove methods that are not used
        $cleaner = new PHPParser_NodeTraverser();

        $cleaner->addVisitor(
            new MethodDisablerVisitor(
                function () {
                    return false;
                }
            )
        );

        $ast = $cleaner->traverse($ast);

        // step 2: implement new methods
        $implementor = new PHPParser_NodeTraverser();

        $implementor->addVisitor(new HydratorMethodsVisitor($originalClass));

        return $implementor->traverse($ast);

        // @todo add interfaces and parent class

        /*
        $interfaces = array('Zend\\Stdlib\\Hydrator\\HydratorInterface');

        if ($originalClass->isInterface()) {
            $interfaces[] = $originalClass->getName();
        } else {
            $classGenerator->setExtendedClass($originalClass->getName());
        }

        $classGenerator->setImplementedInterfaces($interfaces);
        */
    }
}
