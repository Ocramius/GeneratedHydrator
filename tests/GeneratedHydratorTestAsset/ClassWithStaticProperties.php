<?php

declare(strict_types=1);

namespace GeneratedHydratorTestAsset;

/**
 * Base test class used to verify that generated hydrator ignores static properties
 */
class ClassWithStaticProperties
{
    /**
     * @var mixed
     */
    private static $privateStatic;

    /**
     * @var mixed
     */
    protected static $protectedStatic;

    /**
     * @var mixed
     */
    public static $publicStatic;

    /**
     * @var mixed
     */
    private $private;

    /**
     * @var mixed
     */
    private $protected;

    /**
     * @var mixed
     */
    private $public;

    public function getStaticProperties()
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
