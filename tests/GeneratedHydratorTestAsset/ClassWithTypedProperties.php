<?php

declare(strict_types=1);

namespace GeneratedHydratorTestAsset;

/**
 * Test PHP 7.4 hydration and dehydration.
 */
final class ClassWithTypedProperties
{
    private int $property0      = 1;
    private int|null $property1 = 2;
    private int $property2;
    private int|null $property3;
    private int|null $property4 = null;
//phpcs:disable SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
    /** @var mixed */
    private $untyped0;
    /** @var mixed */
    private $untyped1 = null;
//phpcs:enable
}
