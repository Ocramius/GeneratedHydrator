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

namespace GeneratedHydrator\Exception;

/**
 * Base exception class for the generated hydrator manager
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
class InvalidOptionException extends \InvalidArgumentException implements ExceptionInterface {
    public static function valueNotArray($type)
    {
        return new self(sprintf(
            'OPTION_ALLOWED_PROPERTIES is given but it\'s value is of type %s which should be an array.',
            $type
        ));
    }

    public static function invalidValueExpectedString($type, $key)
    {
        return new self(sprintf(
            'Invalid value of type %s found on index %s, expected a string.',
            $type, $key
        ));
    }

    public static function doubleProperty($value)
    {
        return new self(sprintf(
            'Property "%s" was supplied in simple and advanced format, only one is allowed.',
            $value
        ));
    }

    public static function arrayExpected($key, $type)
    {
        return new self(sprintf(
            'Property "%s" was supplied as key, but the value is of type %s and an array was expected.',
            $key, $type
        ));
    }

    public static function invalidValue($property, $key)
    {
        return new self(sprintf(
            'Property "%s" has an invalid value for key "$s".',
            $property, $key
        ));
    }
}
