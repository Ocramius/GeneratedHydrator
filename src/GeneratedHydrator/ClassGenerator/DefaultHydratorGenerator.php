<?php

declare(strict_types=1);

namespace GeneratedHydrator\ClassGenerator;

use CodeGenerationUtils\Visitor\ClassImplementorVisitor;
use GeneratedHydrator\CodeGenerator\Visitor\HydratorMethodsVisitor;
use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\NodeTraverser;
use ReflectionClass;
use Zend\Hydrator\HydratorInterface;
use function explode;

/**
 * Generator for highly performing {@see \Zend\Hydrator\HydratorInterface}
 * for objects
 *
 * {@inheritDoc}
 */
class DefaultHydratorGenerator implements HydratorGenerator
{
    /**
     * Generates an AST of {@see \PhpParser\Node[]} out of a given reflection class
     * and a map of properties to be used to
     *
     * @return Node[]
     */
    public function generate(ReflectionClass $originalClass) : array
    {
        $ast = [new Class_($originalClass->getShortName())];

        $namespace = $originalClass->getNamespaceName();
        if ($namespace) {
            $ast = [new Namespace_(new Name(explode('\\', $namespace)), $ast)];
        }

        $implementor = new NodeTraverser();
        $implementor->addVisitor(new HydratorMethodsVisitor($originalClass));
        $implementor->addVisitor(new ClassImplementorVisitor($originalClass->getName(), [HydratorInterface::class]));

        return $implementor->traverse($ast);
    }
}
