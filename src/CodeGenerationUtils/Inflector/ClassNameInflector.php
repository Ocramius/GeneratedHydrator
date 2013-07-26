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

namespace CodeGenerationUtils\Inflector;

use CodeGenerationUtils\Inflector\Util\ParameterEncoder;

/**
 * {@inheritDoc}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
class ClassNameInflector implements ClassNameInflectorInterface
{
    /**
     * @var string
     */
    protected $generatedClassesNamespace;

    /**
     * @var int
     */
    private $generatedClassMarkerLength;

    /**
     * @var string
     */
    private $generatedClassMarker;

    /**
     * @var \CodeGenerationUtils\Inflector\Util\ParameterEncoder
     */
    private $parameterEncoder;

    /**
     * @param string $generatedClassesNamespace
     */
    public function __construct($generatedClassesNamespace)
    {
        $this->generatedClassesNamespace  = (string) $generatedClassesNamespace;
        $this->generatedClassMarker       = '\\' . static::GENERATED_CLASS_MARKER . '\\';
        $this->generatedClassMarkerLength = strlen($this->generatedClassMarker);
        $this->parameterEncoder           = new ParameterEncoder();
    }

    /**
     * {@inheritDoc}
     */
    public function getUserClassName($className)
    {
        if (false === $position = strrpos($className, $this->generatedClassMarker)) {
            return $className;
        }

        return substr(
            $className,
            $this->generatedClassMarkerLength + $position,
            strrpos($className, '\\') - ($position + $this->generatedClassMarkerLength)
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getGeneratedClassName($className, array $options = array())
    {
        return $this->generatedClassesNamespace
            . $this->generatedClassMarker
            . $this->getUserClassName($className)
            . '\\' . $this->parameterEncoder->encodeParameters($options);
    }

    /**
     * {@inheritDoc}
     */
    public function isGeneratedClassName($className)
    {
        return false !== strrpos($className, $this->generatedClassMarker);
    }
}
