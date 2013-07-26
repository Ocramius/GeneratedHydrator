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

namespace CodeGenerationUtilsTest\Autoloader;

use PHPUnit_Framework_TestCase;
use CodeGenerationUtils\Autoloader\Autoloader;
use CodeGenerationUtils\Inflector\ClassNameInflectorInterface;
use CodeGenerationUtils\Inflector\Util\UniqueIdentifierGenerator;

/**
 * Tests for {@see \CodeGenerationUtils\Autoloader\Autoloader}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
class AutoloaderTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \CodeGenerationUtils\Autoloader\Autoloader
     */
    protected $autoloader;

    /**
     * @var \CodeGenerationUtils\FileLocator\FileLocatorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $fileLocator;

    /**
     * @var \CodeGenerationUtils\Inflector\ClassNameInflectorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $classNameInflector;

    /**
     * @covers \CodeGenerationUtils\Autoloader\Autoloader::__construct
     */
    public function setUp()
    {
        $this->fileLocator        = $this->getMock('CodeGenerationUtils\\FileLocator\\FileLocatorInterface');
        $this->classNameInflector = $this->getMock('CodeGenerationUtils\\Inflector\\ClassNameInflectorInterface');
        $this->autoloader         = new Autoloader($this->fileLocator, $this->classNameInflector);
    }

    /**
     * @covers \CodeGenerationUtils\Autoloader\Autoloader::__invoke
     */
    public function testWillNotAutoloadUserClasses()
    {
        $className = 'Foo\\' . UniqueIdentifierGenerator::getIdentifier('Bar');
        $this
            ->classNameInflector
            ->expects($this->once())
            ->method('isGeneratedClassName')
            ->with($className)
            ->will($this->returnValue(false));

        $this->assertFalse($this->autoloader->__invoke($className));
    }

    /**
     * @covers \CodeGenerationUtils\Autoloader\Autoloader::__invoke
     */
    public function testWillNotAutoloadNonExistingClass()
    {
        $className = 'Foo\\' . UniqueIdentifierGenerator::getIdentifier('Bar');
        $this
            ->classNameInflector
            ->expects($this->once())
            ->method('isGeneratedClassName')
            ->with($className)
            ->will($this->returnValue(true));
        $this
            ->fileLocator
            ->expects($this->once())
            ->method('getGeneratedClassFileName')
            ->will($this->returnValue(__DIR__ . '/non-existing'));

        $this->assertFalse($this->autoloader->__invoke($className));
    }

    /**
     * @covers \CodeGenerationUtils\Autoloader\Autoloader::__invoke
     */
    public function testWillNotAutoloadExistingClass()
    {
        $this->assertFalse($this->autoloader->__invoke(__CLASS__));
    }

    /**
     * @covers \CodeGenerationUtils\Autoloader\Autoloader::__invoke
     */
    public function testWillAutoloadExistingFile()
    {
        $namespace = 'Foo';
        $className = UniqueIdentifierGenerator::getIdentifier('Bar');
        $fqcn      = $namespace . '\\' . $className;
        $fileName  = sys_get_temp_dir() . '/foo_' . uniqid() . '.php';

        file_put_contents($fileName, '<?php namespace ' . $namespace . '; class ' . $className . '{}');

        $this
            ->classNameInflector
            ->expects($this->once())
            ->method('isGeneratedClassName')
            ->with($fqcn)
            ->will($this->returnValue(true));
        $this
            ->fileLocator
            ->expects($this->once())
            ->method('getGeneratedClassFileName')
            ->will($this->returnValue($fileName));

        $this->assertTrue($this->autoloader->__invoke($fqcn));
        $this->assertTrue(class_exists($fqcn, false));
    }
}
