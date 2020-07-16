<?php

declare(strict_types=1);

namespace GeneratedHydrator\ClosureGenerator;

interface GenerateExtractor
{
    public function __invoke(string $className) : callable;
}
