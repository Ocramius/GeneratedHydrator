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

namespace GeneratedHydrator\ClassGenerator;

use CodeGenerationUtils\Visitor\ClassImplementorVisitor;
use GeneratedHydrator\CodeGenerator\Visitor\HydratorMethodsVisitor;
use PhpParser\Builder\Param;
use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Namespace_;
use ReflectionClass;

/**
 * Generator for highly performing {@see \Zend\Hydrator\HydratorInterface}
 * for objects
 *
 * {@inheritDoc}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @author Pierre Rineau <pierre.rineau@makina-corpus.com>
 * @license MIT
 */
class HydratorGenerator implements HydratorGeneratorInterface
{
    /**
     * Generates an AST of {@see \PhpParser\Node[]} out of a given reflection class
     * and a map of properties to be used to
     *
     * @param \ReflectionClass $originalClass
     *
     * @return \PhpParser\Node[]
     */
    public function generate(ReflectionClass $originalClass) : array
    {
        $ast = [new Class_($originalClass->getShortName())];

        if ($namespace = $originalClass->getNamespaceName()) {
            $ast = [new Namespace_(new Name(explode('\\', $namespace)), $ast)];
        }

        $implementor = new NodeTraverser();
        $implementor->addVisitor(new HydratorMethodsVisitor($originalClass));
        $implementor->addVisitor(
            new ClassImplementorVisitor($originalClass->getName(), array('Zend\\Hydrator\\HydratorInterface'))
        );

        return $implementor->traverse($ast);
    }
}
