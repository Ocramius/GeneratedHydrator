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
use CodeGenerationUtils\Visitor\ClassExtensionVisitor;
use CodeGenerationUtils\Visitor\ClassImplementorVisitor;
use CodeGenerationUtils\Visitor\MethodDisablerVisitor;
use GeneratedHydrator\CodeGenerator\Visitor\HydratorMethodsVisitor;
use PhpParser\NodeTraverser;
use ReflectionClass;

/**
 * Generator for highly performing {@see \Zend\Stdlib\Hydrator\HydratorInterface}
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
     * Generates an AST of {@see \PhpParser\Node[]} out of a given reflection class
     * and a map of properties to be used to
     *
     * @param \ReflectionClass $originalClass
     *
     * @return \PhpParser\Node[]
     */
    public function generate(ReflectionClass $originalClass)
    {
        $builder   = new ClassBuilder();

        $ast = $builder->fromReflection($originalClass);

        // step 1: remove methods that are not used
        $cleaner = new NodeTraverser();

        $cleaner->addVisitor(
            new MethodDisablerVisitor(
                function () {
                    return false;
                }
            )
        );

        $ast = $cleaner->traverse($ast);

        // step 2: implement new methods and interfaces, extend original class
        $implementor = new NodeTraverser();

        $implementor->addVisitor(new HydratorMethodsVisitor($originalClass));
        $implementor->addVisitor(new ClassExtensionVisitor($originalClass->getName(), $originalClass->getName()));
        $implementor->addVisitor(
            new ClassImplementorVisitor($originalClass->getName(), array('Zend\\Stdlib\\Hydrator\\HydratorInterface'))
        );

        return $implementor->traverse($ast);
    }
}
