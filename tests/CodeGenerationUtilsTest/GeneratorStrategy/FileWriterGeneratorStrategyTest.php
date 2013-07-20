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

use CodeGenerationUtils\GeneratorStrategy\FileWriterGeneratorStrategy;
use CodeGenerationUtils\Inflector\Util\UniqueIdentifierGenerator;
use PHPParser_Node_Name;
use PHPParser_Node_Stmt_Class;
use PHPParser_Node_Stmt_Namespace;
use PHPUnit_Framework_TestCase;

/**
 * Tests for {@see \CodeGenerationUtils\GeneratorStrategy\FileWriterGeneratorStrategy}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
class FileWriterGeneratorStrategyTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers \CodeGenerationUtils\GeneratorStrategy\FileWriterGeneratorStrategy::__construct
     * @covers \CodeGenerationUtils\GeneratorStrategy\FileWriterGeneratorStrategy::generate
     */
    public function testGenerate()
    {
        $locator   = $this->getMock('CodeGenerationUtils\\FileLocator\\FileLocatorInterface');
        $generator = new FileWriterGeneratorStrategy($locator);
        $tmpFile   = sys_get_temp_dir() . '/FileWriterGeneratorStrategyTest' . uniqid() . '.php';
        $className = UniqueIdentifierGenerator::getIdentifier('Bar');
        $fqcn      = 'Foo\\' . $className;

        $locator
            ->expects($this->any())
            ->method('getProxyFileName')
            ->with($fqcn)
            ->will($this->returnValue($tmpFile));

        $class     = new PHPParser_Node_Stmt_Class($className);
        $namespace = new PHPParser_Node_Stmt_Namespace(new PHPParser_Node_Name('Foo'), array($class));
        $body      = $generator->generate(array($namespace));

        $this->assertGreaterThan(0, strpos($body, $className));
        $this->assertFalse(class_exists($fqcn, false));
        $this->assertTrue(file_exists($tmpFile));

        require $tmpFile;

        $this->assertTrue(class_exists($fqcn, false));
    }
}
