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
