<?php

declare(strict_types=1);

namespace GeneratedHydrator\CodeGenerator\Visitor;

use ReflectionProperty;

use function array_key_exists;

/**
 * @internal
 *
 * @psalm-immutable
 */
final class ObjectProperty
{
    /** @psalm-param non-empty-string $name */
    private function __construct(public string $name, public bool $hasType, public bool $allowsNull, public bool $hasDefault)
    {
    }

    public static function fromReflection(ReflectionProperty $property): self
    {
        /** @psalm-var non-empty-string $propertyName */
        $propertyName  = $property->getName();
        $type          = $property->getType();
        $defaultValues = $property->getDeclaringClass()->getDefaultProperties();

        if ($type === null) {
            return new self($propertyName, false, true, array_key_exists($propertyName, $defaultValues));
        }

        return new self(
            $propertyName,
            true,
            $type->allowsNull(),
            array_key_exists($propertyName, $defaultValues),
        );
    }
}
