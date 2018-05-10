<?php

declare(strict_types=1);

namespace GeneratedHydratorTest\Exception;

use GeneratedHydrator\Exception\DisabledMethodException;
use PHPUnit\Framework\TestCase;

/**
 * Tests for {@see \GeneratedHydrator\Exception\DisabledMethodException}
 */
class DisabledMethodExceptionTest extends TestCase
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
