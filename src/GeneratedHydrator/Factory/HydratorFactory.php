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

namespace GeneratedHydrator\Factory;

use CodeGenerationUtils\Visitor\ClassRenamerVisitor;
use GeneratedHydrator\Configuration;
use GeneratedHydrator\ClassGenerator\HydratorGenerator;
use PHPParser_NodeTraverser;
use ReflectionClass;

/**
 * Factory responsible of producing hydrators
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
class HydratorFactory
{
    /**
     * @var string
     */
    protected $hydratedClassName;

    /**
     * @var bool
     */
    protected $autoGenerate;

    /**
     * @var \CodeGenerationUtils\Inflector\ClassNameInflectorInterface
     */
    protected $inflector;

    /**
     * @var \CodeGenerationUtils\GeneratorStrategy\GeneratorStrategyInterface
     */
    protected $generatorStrategy;

    /**
     * @var \CodeGenerationUtils\Autoloader\AutoloaderInterface
     */
    protected $proxyAutoloader;

    /**
     * Cached generated class names
     *
     * @var string[]
     */
    protected $generatedClasses = array();

    /**
     * Cached proxy class names
     *
     * @var \Zend\Stdlib\Hydrator\HydratorInterface[]
     */
    private $hydrators = array();

    /**
     * @param \GeneratedHydrator\Configuration $configuration
     */
    public function __construct(Configuration $configuration)
    {
        // localizing properties to guarantee immutability
        // @todo can simply clone?
        $this->autoGenerate      = $configuration->doesAutoGenerateProxies();
        $this->inflector         = $configuration->getClassNameInflector();
        $this->generatorStrategy = $configuration->getGeneratorStrategy();
        $this->proxyAutoloader   = $configuration->getProxyAutoloader();
        $this->hydratedClassName = $configuration->getHydratedClassName();
    }

    public function getProxyClass()
    {
        $realClassName  = $this->inflector->getUserClassName($this->hydratedClassName);
        $proxyClassName = $this->inflector->getProxyClassName($realClassName, array('factory' => get_class($this)));

        if ($this->autoGenerate && ! class_exists($proxyClassName)) {
            $generator     = new HydratorGenerator();
            $originalClass = new ReflectionClass($realClassName);
            $generatedAst  = $generator->generate($originalClass);
            $traverser     = new PHPParser_NodeTraverser();

            $traverser->addVisitor(new ClassRenamerVisitor($originalClass, $proxyClassName));

            $this->generatorStrategy->generate($traverser->traverse($generatedAst));
            $this->proxyAutoloader->__invoke($proxyClassName);
        }

        return $proxyClassName;
    }

    /**
     * @param string $className
     *
     * @return \Zend\Stdlib\Hydrator\HydratorInterface
     */
    public function createProxy($className)
    {
        if (isset($this->hydrators[$className])) {
            return $this->hydrators[$className];
        }

        $realClassName  = $this->inflector->getUserClassName($className);
        $proxyClassName = $this->inflector->getProxyClassName($realClassName, array('factory' => get_class($this)));

        if ($this->autoGenerate && ! class_exists($proxyClassName)) {
            $generator     = new HydratorGenerator();
            $originalClass = new ReflectionClass($realClassName);
            $generatedAst  = $generator->generate($originalClass);
            $traverser     = new PHPParser_NodeTraverser();

            $traverser->addVisitor(new ClassRenamerVisitor($originalClass, $proxyClassName));

            $this->generatorStrategy->generate($traverser->traverse($generatedAst));
            $this->proxyAutoloader->__invoke($proxyClassName);
        }

        return $this->hydrators[$className] = new $proxyClassName();
    }
}
