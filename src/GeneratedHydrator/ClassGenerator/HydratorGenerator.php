<?php

declare(strict_types=1);

namespace GeneratedHydrator\ClassGenerator;

use PhpParser\Node;
use ReflectionClass;

/**
 * Interface for the hydrator generator
 */
interface HydratorGenerator
{
    /**
     * Generates an AST of {@see \PhpParser\Node[]} out of a given reflection class
     *
     * @return Node[]
     */
    public function generate(ReflectionClass $originalClass): array;
}
