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

namespace GeneratedHydrator\ClassGenerator\Hydrator\MethodGenerator;

use ProxyManager\Generator\MethodGenerator;
use ReflectionClass;
use Zend\Code\Generator\PropertyGenerator;

/**
 * Method generator for the constructor of a hydrator proxy
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
class Constructor extends MethodGenerator
{
    /**
     * @param \ReflectionClass                                                                $originalClass
     * @param \GeneratedHydrator\ClassGenerator\Hydrator\PropertyGenerator\PropertyAccessor[] $propertyReaders
     * @param \GeneratedHydrator\ClassGenerator\Hydrator\PropertyGenerator\PropertyAccessor[] $propertyWriters
     */
    public function __construct(ReflectionClass $originalClass, array $propertyReaders, array $propertyWriters)
    {
        parent::__construct('__construct');

        $this->setDocblock($originalClass->hasMethod('__construct') ? '{@inheritDoc}' : 'Constructor.');

        if (! empty($propertyReaders) && ! empty($propertyWriters)) {
            $this->setBody(
                $this->getPropertyAccessorsInitialization($propertyReaders, $propertyWriters)
            );
        }
    }

    /**
     * Generates access interceptors initialization code
     *
     * @param \GeneratedHydrator\ClassGenerator\Hydrator\PropertyGenerator\PropertyAccessor[] $propertyReaders
     * @param \GeneratedHydrator\ClassGenerator\Hydrator\PropertyGenerator\PropertyAccessor[] $propertyWriters
     *
     * @return string
     */
    private function getPropertyAccessorsInitialization(array $propertyReaders, array $propertyWriters)
    {
        $body = '';

        foreach ($propertyReaders as $propertyReader) {
            $accessorName     = $propertyReader->getName();
            $originalProperty = $propertyReader->getOriginalProperty();
            $className        = $originalProperty->getDeclaringClass()->getName();
            $property         = $originalProperty->getName();

            $body .= "\n\$this->" . $accessorName . " = \\Closure::bind(function (\$object) {\n"
                . "    return \$object->" . $property . ";\n"
                . "}, null, " . var_export($className, true) . ");";
        }

        foreach ($propertyWriters as $propertyWriter) {
            $accessorName     = $propertyWriter->getName();
            $originalProperty = $propertyWriter->getOriginalProperty();
            $className        = $originalProperty->getDeclaringClass()->getName();
            $property         = $originalProperty->getName();

            $body .= "\n\$this->" . $accessorName . " = \\Closure::bind(function (\$object, \$value) {\n"
                . "    \$object->" . $property . " = \$value;\n"
                . "}, null, " . var_export($className, true) . ");";
        }

        return $body;
    }
}
