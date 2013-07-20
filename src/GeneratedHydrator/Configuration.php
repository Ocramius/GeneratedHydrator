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

namespace GeneratedHydrator;

use CodeGenerationUtils\Autoloader\AutoloaderInterface;
use CodeGenerationUtils\Autoloader\Autoloader;
use CodeGenerationUtils\FileLocator\FileLocator;
use CodeGenerationUtils\GeneratorStrategy\FileWriterGeneratorStrategy;
use CodeGenerationUtils\GeneratorStrategy\GeneratorStrategyInterface;
use CodeGenerationUtils\Inflector\ClassNameInflectorInterface;
use CodeGenerationUtils\Inflector\ClassNameInflector;

/**
 * Base configuration class for the proxy manager - serves as micro disposable DIC/facade
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
class Configuration
{
    const DEFAULT_PROXY_NAMESPACE = 'GeneratedHydratorGeneratedProxy';

    /**
     * @var bool
     */
    protected $autoGenerateProxies = true;

    /**
     * @var string|null
     */
    protected $proxiesTargetDir;

    /**
     * @var string
     */
    protected $proxiesNamespace = self::DEFAULT_PROXY_NAMESPACE;

    /**
     * @var \CodeGenerationUtils\GeneratorStrategy\GeneratorStrategyInterface|null
     */
    protected $generatorStrategy;

    /**
     * @var callable|null
     */
    protected $proxyAutoloader;

    /**
     * @var \CodeGenerationUtils\Inflector\ClassNameInflectorInterface|null
     */
    protected $classNameInflector;

    /**
     * @param bool $autoGenerateProxies
     */
    public function setAutoGenerateProxies($autoGenerateProxies)
    {
        $this->autoGenerateProxies = (bool) $autoGenerateProxies;
    }

    /**
     * @return bool
     */
    public function doesAutoGenerateProxies()
    {
        return $this->autoGenerateProxies;
    }

    /**
     * @param \CodeGenerationUtils\Autoloader\AutoloaderInterface $proxyAutoloader
     */
    public function setProxyAutoloader(AutoloaderInterface $proxyAutoloader)
    {
        $this->proxyAutoloader = $proxyAutoloader;
    }

    /**
     * @return \CodeGenerationUtils\Autoloader\AutoloaderInterface
     */
    public function getProxyAutoloader()
    {
        if (null === $this->proxyAutoloader) {
            $this->proxyAutoloader = new Autoloader(
                new FileLocator($this->getProxiesTargetDir()),
                $this->getClassNameInflector()
            );
        }

        return $this->proxyAutoloader;
    }

    /**
     * @param string $proxiesNamespace
     */
    public function setProxiesNamespace($proxiesNamespace)
    {
        $this->proxiesNamespace = $proxiesNamespace;
    }

    /**
     * @return string
     */
    public function getProxiesNamespace()
    {
        return $this->proxiesNamespace;
    }

    /**
     * @param string $proxiesTargetDir
     */
    public function setProxiesTargetDir($proxiesTargetDir)
    {
        $this->proxiesTargetDir = (string) $proxiesTargetDir;
    }

    /**
     * @return null|string
     */
    public function getProxiesTargetDir()
    {
        if (null === $this->proxiesTargetDir) {
            $this->proxiesTargetDir = sys_get_temp_dir();
        }

        return $this->proxiesTargetDir;
    }

    /**
     * @param \CodeGenerationUtils\GeneratorStrategy\GeneratorStrategyInterface $generatorStrategy
     */
    public function setGeneratorStrategy(GeneratorStrategyInterface $generatorStrategy)
    {
        $this->generatorStrategy = $generatorStrategy;
    }

    /**
     * @return \CodeGenerationUtils\GeneratorStrategy\GeneratorStrategyInterface
     */
    public function getGeneratorStrategy()
    {
        if (null === $this->generatorStrategy) {
            $this->generatorStrategy = new FileWriterGeneratorStrategy(new FileLocator($this->getProxiesTargetDir()));
        }

        return $this->generatorStrategy;
    }

    /**
     * @param \CodeGenerationUtils\Inflector\ClassNameInflectorInterface $classNameInflector
     */
    public function setClassNameInflector(ClassNameInflectorInterface $classNameInflector)
    {
        $this->classNameInflector = $classNameInflector;
    }

    /**
     * @return \CodeGenerationUtils\Inflector\ClassNameInflectorInterface
     */
    public function getClassNameInflector()
    {
        if (null === $this->classNameInflector) {
            $this->classNameInflector = new ClassNameInflector($this->getProxiesNamespace());
        }

        return $this->classNameInflector;
    }
}
