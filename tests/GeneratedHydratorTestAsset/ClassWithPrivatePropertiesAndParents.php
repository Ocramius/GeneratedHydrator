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

declare(strict_types=1);

namespace GeneratedHydratorTestAsset;

/**
 * Base test class to play around with parent class private properties
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @author Pierre Rineau <pierre.rineau@makina-corpus.com>
 * @license MIT
 */
class ClassWithPrivatePropertiesAndParents extends ClassWithPrivatePropertiesAndParent
{
    private $property0 = 'property0_fromChild';

    private $property20 = 'property20_fromChild';

    private $property30 = 'property30';

    protected $property31 = 'property31';

    public $property32 = 'property32';

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
     * @param string $property30
     */
    public function setProperty30($property30)
    {
        $this->property30 = $property30;
    }

    /**
     * @return string
     */
    public function getProperty30()
    {
        return $this->property30;
    }

    /**
     * @param string $property31
     */
    public function setProperty31($property31)
    {
        $this->property31 = $property31;
    }

    /**
     * @return string
     */
    public function getProperty31()
    {
        return $this->property31;
    }

    /**
     * @param string $property32
     */
    public function setProperty32($property32)
    {
        $this->property32 = $property32;
    }

    /**
     * @return string
     */
    public function getProperty32()
    {
        return $this->property32;
    }
}
