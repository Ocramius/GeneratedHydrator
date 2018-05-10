<?php

declare(strict_types=1);

namespace GeneratedHydratorTestAsset;

/**
 * Base test class to play around with parent class private properties
 */
class ClassWithPrivatePropertiesAndParent extends ClassWithPrivateProperties
{
    private $property0 = 'property0_fromChild';

    private $property1 = 'property1_fromChild';

    private $property20 = 'property20';

    protected $property21 = 'property21';

    public $property22 = 'property22';

    /**
     * @param string $property0
     */
    public function setProperty0($property0)
    {
        $this->property0 = $property0;
    }

    /**
     * @return string
     */
    public function getProperty0()
    {
        return $this->property0;
    }

    /**
     * @param string $property1
     */
    public function setProperty1($property1)
    {
        $this->property1 = $property1;
    }

    /**
     * @return string
     */
    public function getProperty1()
    {
        return $this->property1;
    }

    /**
     * @param string $property20
     */
    public function setProperty20($property20)
    {
        $this->property20 = $property20;
    }

    /**
     * @return string
     */
    public function getProperty20()
    {
        return $this->property20;
    }

    /**
     * @param string $property21
     */
    public function setProperty21($property21)
    {
        $this->property21 = $property21;
    }

    /**
     * @return string
     */
    public function getProperty21()
    {
        return $this->property21;
    }

    /**
     * @param string $property22
     */
    public function setProperty22($property22)
    {
        $this->property22 = $property22;
    }

    /**
     * @return string
     */
    public function getProperty22()
    {
        return $this->property22;
    }
}
