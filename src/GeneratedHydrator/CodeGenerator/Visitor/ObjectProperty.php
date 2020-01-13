<?php

declare(strict_types=1);

namespace GeneratedHydrator\CodeGenerator\Visitor;

use function version_compare;

/**
 * @internal
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
        $this->name = $name;
        $this->type = $type;
        $this->required = $required;
        $this->hasDefault = $hasDefault;
    }

    /**
     * Create instance from reflection object
     */
    public static function fromReflection(\ReflectionProperty $property): self
    {
        $propertyName = $property->getName();

        if (0 <= version_compare(PHP_VERSION, '7.4.0') && ($type = $property->getType())) {
            // Check if property have a default value. It seems there is no
            // other way, it probably will create a confusion between properties
            // defaulting to null and those who will remain unitilialized.
            $defaults = $property->getDeclaringClass()->getDefaultProperties();

            return new self(
                $propertyName,
                $type->getName(),
                !$type->allowsNull(),
                isset($defaults[$propertyName])
            );
        }

        return new self($propertyName);
    }
}
