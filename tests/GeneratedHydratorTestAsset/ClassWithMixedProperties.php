<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license.
 */

namespace GeneratedHydratorTestAsset;

/**
 * Base test class to play around with mixed visibility properties
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
class ClassWithMixedProperties
{
    public $publicProperty0       = 'publicProperty0';

    public $publicProperty1       = 'publicProperty1';

    public $publicProperty2       = 'publicProperty2';

    protected $protectedProperty0 = 'protectedProperty0';

    protected $protectedProperty1 = 'protectedProperty1';

    protected $protectedProperty2 = 'protectedProperty2';

    private $privateProperty0     = 'privateProperty0';

    private $privateProperty1     = 'privateProperty1';

    private $privateProperty2     = 'privateProperty2';

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
