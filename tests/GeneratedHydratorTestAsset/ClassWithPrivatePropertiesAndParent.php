<?php

declare(strict_types=1);

namespace GeneratedHydratorTestAsset;

/**
 * Base test class to play around with parent class private properties
 */
class ClassWithPrivatePropertiesAndParent extends ClassWithPrivateProperties
{
    /** @var string */
    private $property0 = 'property0_fromChild';

    /** @var string */
    private $property1 = 'property1_fromChild';

    /** @var string */
    private $property20 = 'property20';

    /** @var string */
    protected $property21 = 'property21';

    /** @var string */
    public $property22 = 'property22';

    public function setProperty0(string $property0) : void
    {
        $this->property0 = $property0;
    }

    public function getProperty0() : string
    {
        return $this->property0;
    }

    public function setProperty1(string $property1) : void
    {
        $this->property1 = $property1;
    }

    public function getProperty1() : string
    {
        return $this->property1;
    }

    public function setProperty20(string $property20) : void
    {
        $this->property20 = $property20;
    }

    public function getProperty20() : string
    {
        return $this->property20;
    }

    public function setProperty21(string $property21) : void
    {
        $this->property21 = $property21;
    }

    public function getProperty21() : string
    {
        return $this->property21;
    }

    public function setProperty22(string $property22) : void
    {
        $this->property22 = $property22;
    }

    public function getProperty22() : string
    {
        return $this->property22;
    }
}
