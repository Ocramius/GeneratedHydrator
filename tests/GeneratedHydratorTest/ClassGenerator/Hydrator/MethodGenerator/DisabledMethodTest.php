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

namespace GeneratedHydratorTest\ClassGenerator\Hydrator\MethodGenerator;

use PHPUnit_Framework_TestCase;
use GeneratedHydrator\ClassGenerator\Hydrator\MethodGenerator\DisabledMethod;

/**
 * Tests for {@see \GeneratedHydrator\ClassGenerator\Hydrator\MethodGenerator\DisabledMethod}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 *
 * @covers \GeneratedHydrator\ClassGenerator\Hydrator\MethodGenerator\DisabledMethod
 */
class DisabledMethodTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers \GeneratedHydrator\ClassGenerator\Hydrator\MethodGenerator\DisabledMethod::generate
     */
    public function testGeneratedStructure()
    {
        $disabledMethod = new DisabledMethod('foo');

        $this->assertStringMatchesFormat(
            '%athrow \\GeneratedHydrator\\Exception\\DisabledMethodException::disabledMethod(__METHOD__);%a',
            $disabledMethod->generate()
        );
    }
}
