<?php

namespace GeneratedHydrator\ClassGenerator;

use ReflectionClass;

/**
 * Interface for the hydrator generator
 *
 * @author Magnus Nordlander <magnus@fervo.se>
 * @license MIT
 */
interface HydratorGeneratorInterface
{
    /**
     * Generates an AST of {@see \PhpParser\Node[]} out of a given reflection class
     *
     * @param \ReflectionClass $originalClass
     *
     * @return \PhpParser\Node[]
     */
    public function generate(ReflectionClass $originalClass);
}
