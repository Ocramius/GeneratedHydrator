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

namespace CodeGenerationUtilsTest\GeneratorStrategy;

use CodeGenerationUtils\GeneratorStrategy\BaseGeneratorStrategy;
use CodeGenerationUtils\Inflector\Util\UniqueIdentifierGenerator;
use PHPParser_Node_Stmt_Class;
use PHPUnit_Framework_TestCase;

/**
 * Tests for {@see \CodeGenerationUtils\GeneratorStrategy\BaseGeneratorStrategy}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
class BaseGeneratorStrategyTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers \CodeGenerationUtils\GeneratorStrategy\BaseGeneratorStrategy::generate
     */
    public function testGenerate()
    {
        $strategy       = new BaseGeneratorStrategy();
        $className      = UniqueIdentifierGenerator::getIdentifier('Foo');
        $generated      = $strategy->generate(array(new PHPParser_Node_Stmt_Class($className)));

        $this->assertGreaterThan(0, strpos($generated, $className));
    }

    /**
     * @covers \CodeGenerationUtils\GeneratorStrategy\BaseGeneratorStrategy::setPrettyPrinter
     * @covers \CodeGenerationUtils\GeneratorStrategy\BaseGeneratorStrategy::getPrettyPrinter
     */
    public function testSetPrettyPrinter()
    {
        $strategy = new BaseGeneratorStrategy();

        $prettyPrinter = $this->getMock('PHPParser_PrettyPrinterAbstract');

        $prettyPrinter
            ->expects($this->once())
            ->method('prettyPrint')
            ->with(array('bar'))
            ->will($this->returnValue('foo'));

        $strategy->setPrettyPrinter($prettyPrinter);

        $this->assertSame('foo', $strategy->generate(array('bar')));
    }
}
