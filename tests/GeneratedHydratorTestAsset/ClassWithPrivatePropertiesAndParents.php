<?php

declare(strict_types=1);

namespace GeneratedHydratorTestAsset;

/**
 * Base test class to play around with parent class private properties
 */
class ClassWithPrivatePropertiesAndParents extends ClassWithPrivatePropertiesAndParent
{
    /** @var string */
    private $property0 = 'property0_fromChild';

    /** @var string */
    private $property20 = 'property20_fromChild';

    /** @var string */
    private $property30 = 'property30';

    /** @var string */
    protected $property31 = 'property31';

    /** @var string */
    public $property32 = 'property32';

    public function setProperty0(string $property0) : void
    {
        $this->property0 = $property0;
    }

    public function getProperty0() : string
    {
        return $this->property0;
    }

    public function setProperty20(string $property20) : void
    {
        $this->property20 = $property20;
    }

    public function getProperty20() : string
    {
        return $this->property20;
    }

    public function setProperty30(string $property30) : void
    {
        $this->property30 = $property30;
    }

    public function getProperty30() : string
    {
        return $this->property30;
    }

    public function setProperty31(string $property31) : void
    {
        $this->property31 = $property31;
    }

    public function getProperty31() : string
    {
        return $this->property31;
    }

    public function setProperty32(string $property32) : void
    {
        $this->property32 = $property32;
    }

    public function getProperty32() : string
    {
        return $this->property32;
    }
}
