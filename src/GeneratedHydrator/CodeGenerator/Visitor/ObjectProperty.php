<?php

declare(strict_types=1);

namespace GeneratedHydrator\CodeGenerator\Visitor;

use Doctrine\Common\Annotations\AnnotationReader;
use GeneratedHydrator\Annotation\MappedFrom;
use ReflectionProperty;
use function array_key_exists;
use function class_exists;

/**
 * @internal
 *
 * @psalm-immutable
 */
final class ObjectProperty
{
    public bool $hasType;
    public bool $hasDefault;
    public bool $allowsNull;
    /** @psalm-var non-empty-string */
    public string $name;
    /** @psalm-var non-empty-string */
    public string $mappedFrom;

    /** @psalm-param non-empty-string $name */
    private function __construct(string $name, bool $hasType, bool $allowsNull, bool $hasDefault, string $mappedFrom)
    {
        $this->name       = $name;
        $this->hasType    = $hasType;
        $this->allowsNull = $allowsNull;
        $this->hasDefault = $hasDefault;
        $this->mappedFrom = $mappedFrom;
    }

    public static function fromReflection(ReflectionProperty $property) : self
    {
        /** @psalm-var non-empty-string $propertyName */
        $propertyName  = $property->getName();
        $mappedFrom  = $property->getName();
        $type          = $property->getType();
        $defaultValues = $property->getDeclaringClass()->getDefaultProperties();

        if (class_exists(AnnotationReader::class) === true) {
            $reader = new AnnotationReader();
            $mappedFromAnnotation = $reader->getPropertyAnnotation($property, MappedFrom::class);
            $mappedFrom = $mappedFromAnnotation->name ?? $propertyName;
        }

        if ($type === null) {
            return new self($propertyName, false, true, array_key_exists($propertyName, $defaultValues), $mappedFrom);
        }

        return new self(
            $propertyName,
            true,
            $type->allowsNull(),
            array_key_exists($propertyName, $defaultValues),
            $mappedFrom
        );
    }
}
