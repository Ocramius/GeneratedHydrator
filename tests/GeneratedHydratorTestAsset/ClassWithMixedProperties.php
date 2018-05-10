<?php

declare(strict_types=1);

namespace GeneratedHydratorTestAsset;

/**
 * Base test class to play around with mixed visibility properties
 */
class ClassWithMixedProperties
{
    public $publicProperty0 = 'publicProperty0';

    public $publicProperty1 = 'publicProperty1';

    public $publicProperty2 = 'publicProperty2';

    protected $protectedProperty0 = 'protectedProperty0';

    protected $protectedProperty1 = 'protectedProperty1';

    protected $protectedProperty2 = 'protectedProperty2';

    private $privateProperty0 = 'privateProperty0';

    private $privateProperty1 = 'privateProperty1';

    private $privateProperty2 = 'privateProperty2';

    /**
     * @param string $privateProperty0
     */
    public function setPrivateProperty0($privateProperty0)
    {
        $this->privateProperty0 = $privateProperty0;
    }

    /**
     * @return string
     */
    public function getPrivateProperty0()
    {
        return $this->privateProperty0;
    }

    /**
     * @param string $privateProperty1
     */
    public function setPrivateProperty1($privateProperty1)
    {
        $this->privateProperty1 = $privateProperty1;
    }

    /**
     * @return string
     */
    public function getPrivateProperty1()
    {
        return $this->privateProperty1;
    }

    /**
     * @param string $privateProperty2
     */
    public function setPrivateProperty2($privateProperty2)
    {
        $this->privateProperty2 = $privateProperty2;
    }

    /**
     * @return string
     */
    public function getPrivateProperty2()
    {
        return $this->privateProperty2;
    }

    /**
     * @param string $protectedProperty0
     */
    public function setProtectedProperty0($protectedProperty0)
    {
        $this->protectedProperty0 = $protectedProperty0;
    }

    /**
     * @return string
     */
    public function getProtectedProperty0()
    {
        return $this->protectedProperty0;
    }

    /**
     * @param string $protectedProperty1
     */
    public function setProtectedProperty1($protectedProperty1)
    {
        $this->protectedProperty1 = $protectedProperty1;
    }

    /**
     * @return string
     */
    public function getProtectedProperty1()
    {
        return $this->protectedProperty1;
    }

    /**
     * @param string $protectedProperty2
     */
    public function setProtectedProperty2($protectedProperty2)
    {
        $this->protectedProperty2 = $protectedProperty2;
    }

    /**
     * @return string
     */
    public function getProtectedProperty2()
    {
        return $this->protectedProperty2;
    }

    /**
     * @param string $publicProperty0
     */
    public function setPublicProperty0($publicProperty0)
    {
        $this->publicProperty0 = $publicProperty0;
    }

    /**
     * @return string
     */
    public function getPublicProperty0()
    {
        return $this->publicProperty0;
    }

    /**
     * @param string $publicProperty1
     */
    public function setPublicProperty1($publicProperty1)
    {
        $this->publicProperty1 = $publicProperty1;
    }

    /**
     * @return string
     */
    public function getPublicProperty1()
    {
        return $this->publicProperty1;
    }

    /**
     * @param string $publicProperty2
     */
    public function setPublicProperty2($publicProperty2)
    {
        $this->publicProperty2 = $publicProperty2;
    }

    /**
     * @return string
     */
    public function getPublicProperty2()
    {
        return $this->publicProperty2;
    }
}
