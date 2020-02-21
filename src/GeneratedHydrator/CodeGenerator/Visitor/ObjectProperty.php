<?php

declare(strict_types=1);

namespace GeneratedHydrator\CodeGenerator\Visitor;

use ReflectionProperty;
use const PHP_VERSION_ID;

/**
 * @internal
 *
 * @psalm-immutable
 */
final class ObjectProperty
{
    /** @var ?string */
    public $type = null;

    /** @var bool */
    public $hasDefault = false;

    /** @var bool */
    public $required = false;

    /** @var string  */
    public $name;

    private function __construct(string $name, ?string $type = null, bool $required = false, bool $hasDefault = false)
    {
        $this->name       = $name;
        $this->type       = $type;
        $this->required   = $required;
        $this->hasDefault = $hasDefault;
    }

    /**
     * Create instance from reflection object
     */
    public static function fromReflection(ReflectionProperty $property) : self
    {
        $propertyName = $property->getName();

        if (PHP_VERSION_ID < 70400) {
            return new self($propertyName);
        }

        $type = $property->getType();

        if ($type === null) {
            return new self($propertyName);
        }

        // Check if property have a default value. It seems there is no
        // other way, it probably will create a confusion between properties
        // defaulting to null and those who will remain unitilialized.
        $defaults = $property->getDeclaringClass()->getDefaultProperties();

        return new self(
            $propertyName,
            $type->getName(),
            ! $type->allowsNull(),
            isset($defaults[$propertyName])
        );
    }
}
