<?php

declare(strict_types=1);

namespace GeneratedHydratorTestAsset;

/**
 * Base test class used to verify that generated hydrator ignores static properties
 */
class ClassWithStaticProperties
{
    private static mixed $privateStatic = null;

    protected static mixed $protectedStatic = null;

    public static mixed $publicStatic = null;

    private mixed $private = null;

    private mixed $protected = null;

    private mixed $public = null;

    /** @return mixed[] */
    public function getStaticProperties(): array
    {
        return [
            'privateStatic'   => self::$privateStatic,
            'protectedStatic' => self::$protectedStatic,
            'publicStatic'    => self::$publicStatic,
            'private'         => $this->private,
            'protected'       => $this->protected,
            'public'          => $this->public,
        ];
    }
}
