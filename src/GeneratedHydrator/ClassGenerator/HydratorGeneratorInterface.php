<?php

namespace GeneratedHydrator\ClassGenerator;

use PhpParser\Node;
use ReflectionClass;

/**
 * Interface for the hydrator generator
 */
interface HydratorGeneratorInterface
{
    /**
     * Generates an AST of {@see \PhpParser\Node[]} out of a given reflection class
     *
     * @param ReflectionClass $originalClass
     *
     * @return Node[]
     */
    public function generate(ReflectionClass $originalClass) : array;
}
