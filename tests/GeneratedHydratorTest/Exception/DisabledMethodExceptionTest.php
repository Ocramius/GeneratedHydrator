<?php

declare(strict_types=1);

namespace GeneratedHydratorTest\Exception;

use PHPUnit_Framework_TestCase;
use GeneratedHydrator\Exception\DisabledMethodException;

/**
 * Tests for {@see \GeneratedHydrator\Exception\DisabledMethodException}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
class DisabledMethodExceptionTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers \GeneratedHydrator\Exception\DisabledMethodException::disabledMethod
     */
    public function testDisabledMethod()
    {
        $exception = DisabledMethodException::disabledMethod('foo::bar');

        self::assertSame('Method "foo::bar" is forcefully disabled', $exception->getMessage());
    }
}
