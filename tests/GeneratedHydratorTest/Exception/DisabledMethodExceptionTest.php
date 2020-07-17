<?php

declare(strict_types=1);

namespace GeneratedHydratorTest\Exception;

use GeneratedHydrator\Exception\DisabledMethod;
use PHPUnit\Framework\TestCase;

/**
 * Tests for {@see \GeneratedHydrator\Exception\DisabledMethodException}
 */
class DisabledMethodExceptionTest extends TestCase
{
    /**
     * @covers \GeneratedHydrator\Exception\DisabledMethod::create
     */
    public function testDisabledMethod(): void
    {
        $exception = DisabledMethod::create('foo::bar');

        self::assertSame('Method "foo::bar" is forcefully disabled', $exception->getMessage());
    }
}
