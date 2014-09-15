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
use PhpParser\NodeTraverser;
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
     * @var \GeneratedHydrator\Configuration
     */
    private $configuration;

    /**
     * @param \GeneratedHydrator\Configuration $configuration
     */
    public function __construct(Configuration $configuration)
    {
        $this->configuration = clone $configuration;
    }

    /**
     * Retrieves the generated hydrator FQCN
     *
     * @return string
     */
    public function getHydratorClass()
    {
        $inflector         = $this->configuration->getClassNameInflector();
        $realClassName     = $inflector->getUserClassName($this->configuration->getHydratedClassName());
        $hydratorClassName = $inflector->getGeneratedClassName($realClassName, array('factory' => get_class($this)));

        if (! class_exists($hydratorClassName) && $this->configuration->doesAutoGenerateProxies()) {
            $generator     = new HydratorGenerator();
            $originalClass = new ReflectionClass($realClassName);
            $generatedAst  = $generator->generate($originalClass);
            $traverser     = new NodeTraverser();

            $traverser->addVisitor(new ClassRenamerVisitor($originalClass, $hydratorClassName));

            $this->configuration->getGeneratorStrategy()->generate($traverser->traverse($generatedAst));
            $this->configuration->getGeneratedClassAutoloader()->__invoke($hydratorClassName);
        }

        return $hydratorClassName;
    }
}
