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

use CodeGenerationUtils\GeneratorStrategy\EvaluatingGeneratorStrategy;
use CodeGenerationUtils\Inflector\Util\UniqueIdentifierGenerator;
use PHPParser_Node_Stmt_Class;
use PHPUnit_Framework_TestCase;

/**
 * Tests for {@see \CodeGenerationUtils\GeneratorStrategy\EvaluatingGeneratorStrategy}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
class EvaluatingGeneratorStrategyTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers \CodeGenerationUtils\GeneratorStrategy\EvaluatingGeneratorStrategy::generate
     * @covers \CodeGenerationUtils\GeneratorStrategy\EvaluatingGeneratorStrategy::__construct
     */
    public function testGenerate()
    {
        $strategy       = new EvaluatingGeneratorStrategy();
        $className      = UniqueIdentifierGenerator::getIdentifier('Foo');
        $generated      = $strategy->generate(array(new PHPParser_Node_Stmt_Class($className)));

        $this->assertGreaterThan(0, strpos($generated, $className));
        $this->assertTrue(class_exists($className, false));
    }

    /**
     * @covers \CodeGenerationUtils\GeneratorStrategy\EvaluatingGeneratorStrategy::generate
     * @covers \CodeGenerationUtils\GeneratorStrategy\EvaluatingGeneratorStrategy::__construct
     */
    public function testGenerateWithDisabledEval()
    {
        if (! ini_get('suhosin.executor.disable_eval')) {
            $this->markTestSkipped('Ini setting "suhosin.executor.disable_eval" is needed to run this test');
        }

        $strategy       = new EvaluatingGeneratorStrategy();
        $className      = 'Foo' . uniqid();
        $generated      = $strategy->generate(array(new PHPParser_Node_Stmt_Class($className)));

        $this->assertGreaterThan(0, strpos($generated, $className));
        $this->assertTrue(class_exists($className, false));
    }
}
