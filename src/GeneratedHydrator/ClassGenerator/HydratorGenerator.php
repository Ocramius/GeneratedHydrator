<?php

declare(strict_types=1);

namespace GeneratedHydrator\ClassGenerator;

use CodeGenerationUtils\Visitor\ClassImplementorVisitor;
use GeneratedHydrator\CodeGenerator\Visitor\HydratorMethodsVisitor;
use PhpParser\NodeTraverser;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Namespace_;
use ReflectionClass;
use Zend\Hydrator\HydratorInterface;

/**
 * Generator for highly performing {@see \Zend\Hydrator\HydratorInterface}
 * for objects
 *
 * {@inheritDoc}
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
        $implementor->addVisitor(new ClassImplementorVisitor($originalClass->getName(), [HydratorInterface::class]));

        return $implementor->traverse($ast);
    }
}
