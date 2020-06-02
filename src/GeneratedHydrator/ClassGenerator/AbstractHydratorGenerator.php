<?php

declare(strict_types=1);

namespace GeneratedHydrator\ClassGenerator;

use CodeGenerationUtils\Visitor\ClassExtensionVisitor;
use GeneratedHydrator\CodeGenerator\Visitor\AbstractHydratorMethodsVisitor;
use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\NodeTraverser;
use ReflectionClass;
use Zend\Hydrator\AbstractHydrator;
use function explode;

/**
 * Generator for highly performing {@see \Zend\Hydrator\HydratorInterface}
 * for objects
 *
 * {@inheritDoc}
 */
class AbstractHydratorGenerator implements HydratorGenerator
{
    /**
     * Generates an AST of {@see \PhpParser\Node[]} out of a given reflection class
     * and a map of properties to be used to
     *
     * @return Node[]
     */
    public function generate(ReflectionClass $originalClass): array
    {
        $ast = [new Class_($originalClass->getShortName())];

        $namespace = $originalClass->getNamespaceName();
        if ($namespace) {
            $ast = [new Namespace_(new Name(explode('\\', $namespace)), $ast)];
        }

        $implementor = new NodeTraverser();
        $implementor->addVisitor(new AbstractHydratorMethodsVisitor($originalClass));
        $implementor->addVisitor(new ClassExtensionVisitor($originalClass->getName(), AbstractHydrator::class));

        return $implementor->traverse($ast);
    }
}
