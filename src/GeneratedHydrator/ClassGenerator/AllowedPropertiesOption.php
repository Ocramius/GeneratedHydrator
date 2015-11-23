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

namespace GeneratedHydrator\ClassGenerator;

use GeneratedHydrator\Exception\InvalidOptionException;
use ReflectionClass;

/**
 * Value Object to configure how and which properties get hydrated or extracted.
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
class AllowedPropertiesOption {
    /**
     * When this option is passed, only the properties in the given array are
     * hydrated and extracted.
     */
    const OPTION_ALLOWED_PROPERTIES = 'allowedProperties';

    /**
     * @var PropertyAccessor[]
     */
    private $propertyNames;

    /**
     * @var array Holds configuration for the object properties.
     */
    private $allowedProperties;

    public function __construct(ReflectionClass $reflectedClass, array $options)
    {
        $this->propertyNames = array_map(function(\ReflectionProperty $prop) {
            return $prop->name;
        }, $reflectedClass->getProperties());

        $this->allowedProperties = $this->expandAllowedProperties($options);
    }

    /**
     * Returns an array with properties as keys and hydrate/extract information
     * as values.
     *
     * @param array $options
     */
    private function expandAllowedProperties(array $options)
    {
        $allowedProperties = [];

        // Option was not given
        if (! isset($options[static::OPTION_ALLOWED_PROPERTIES])) {
            foreach ($this->propertyNames as $propertyName) {
                $allowedProperties[$propertyName] = [
                    'extract' => true,
                    'hydrate' => true
                ];
            }

            return $allowedProperties;
        }

        if (! is_array($options[static::OPTION_ALLOWED_PROPERTIES])) {
            throw InvalidOptionException::valueNotArray(gettype($options[static::OPTION_ALLOWED_PROPERTIES]));
        }

        // Option was given
        foreach ($options[static::OPTION_ALLOWED_PROPERTIES] as $k => $v) {
            // simple format
            if (is_int($k)) {
                $this->makeSimpleFormat($k, $v, $allowedProperties);

                continue;
            }

            // advanced format
            if (is_string($k)) {
                $this->makeAdvancedFormat($k, $v, $allowedProperties);
            }
        }

        // Disable all properties which are not specified in the allowedProperties
        foreach ($this->propertyNames as $propertyName) {
            if (! in_array($propertyName, array_keys($allowedProperties))) {
                $allowedProperties[$propertyName] = [
                    'extract' => false,
                    'hydrate' => false
                ];
            }
        }

        return $allowedProperties;
    }

    private function makeSimpleFormat($key, $value, &$allowedProperties)
    {
        if (! is_string($value)) {
            throw InvalidOptionException::invalidValueExpectedString(gettype($value), $key);
        }

        if (in_array($value, array_keys($allowedProperties))) {
            throw InvalidOptionException::doubleProperty($value);
        }

        $allowedProperties[$value] = [
            'extract' => true,
            'hydrate' => true
        ];
    }

    private function makeAdvancedFormat($key, $value, &$allowedProperties)
    {
        if (! is_array($value)) {
            throw InvalidOptionException::arrayExpected($key, gettype($value));
        }

        if (in_array($key, $allowedProperties)) {
            throw InvalidOptionException::doubleProperty($value);
        }

        $validateOptionConfigurationKey = function($property, &$array, $key) {
            if (! isset($array[$key])) {
                $array[$key] = false;
            }

            if (! in_array($array[$key], [true, false, 'optional'])) {
                throw InvalidOptionException::invalidValue($property, $key);
            }
        };

        $validateOptionConfigurationKey($key, $value, 'extract');
        $validateOptionConfigurationKey($key, $value, 'hydrate');

        $allowedProperties[$key] = $value;
    }

    public function getAllowedProperties()
    {
        return $this->allowedProperties;
    }
}
