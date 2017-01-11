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

declare(strict_types=1);

namespace GeneratedHydrator\CodeGenerator\Visitor;

use GeneratedHydrator\ClassGenerator\Hydrator\PropertyGenerator\PropertyAccessor;
use PhpParser\Node;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\NodeVisitorAbstract;
use PhpParser\ParserFactory;
use ReflectionClass;
use ReflectionProperty;

/**
 * Replaces methods `__construct`, `hydrate` and `extract` in the classes of the given AST
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
class HydratorMethodsVisitor extends NodeVisitorAbstract
{
    /**
     * @var ReflectionClass
     */
    private $reflectedClass;

    /**
     * @var ReflectionProperty[]
     */
    private $accessibleProperties;

    /**
     * @var PropertyAccessor[]
     */
    private $propertyWriters = [];

    /**
     * @param ReflectionClass $reflectedClass
     */
    public function __construct(ReflectionClass $reflectedClass)
    {
        $this->reflectedClass       = $reflectedClass;
        $this->accessibleProperties = $this->getProtectedProperties($reflectedClass);

        foreach ($this->getPrivateProperties($reflectedClass) as $property) {
            $this->propertyWriters[$property->getName()] = new PropertyAccessor($property, 'Writer');
        }
    }

    /**
     * @param Node $node
     *
     * @return null|Class_
     */
    public function leaveNode(Node $node)
    {
        if (! $node instanceof Class_) {
            return null;
        }

        $this->replaceConstructor($this->findOrCreateMethod($node, '__construct'));
        $this->replaceHydrate($this->findOrCreateMethod($node, 'hydrate'));
        $this->replaceExtract($this->findOrCreateMethod($node, 'extract'));

        return $node;
    }

    /**
     * @param ClassMethod $method
     */
    private function replaceConstructor(ClassMethod $method)
    {
        $method->params = [];

        $bodyParts = [];

        foreach ($this->propertyWriters as $propertyWriter) {
            $accessorName     = $propertyWriter->props[0]->name;
            $originalProperty = $propertyWriter->getOriginalProperty();
            $className        = $originalProperty->getDeclaringClass()->getName();
            $property         = $originalProperty->getName();

            $bodyParts[] = "\$this->" . $accessorName . " = \\Closure::bind(function (\$object, \$value) {\n"
                . "    \$object->" . $property . " = \$value;\n"
                . '}, null, ' . var_export($className, true) . ');';
        }

        $method->stmts = (new ParserFactory)
            ->create(ParserFactory::ONLY_PHP7)
            ->parse('<?php ' . implode("\n", $bodyParts));
    }

    /**
     * @param ClassMethod $method
     */
    private function replaceHydrate(ClassMethod $method)
    {
        $method->params = [
            new Param('data', null, 'array'),
            new Param('object'),
        ];

        $body = '';

        foreach ($this->accessibleProperties as $accessibleProperty) {
            $body .= '$object->'
                . $accessibleProperty->getName()
                . ' = $data['
                . var_export($accessibleProperty->getName(), true)
                . "];\n";
        }

        foreach ($this->propertyWriters as $propertyWriter) {
            $body .= '$this->'
                . $propertyWriter->props[0]->name
                . '->__invoke($object, $data['
                . var_export($propertyWriter->getOriginalProperty()->getName(), true)
                . "]);\n";
        }

        $body .= "\nreturn \$object;";

        $method->stmts = (new ParserFactory())
            ->create(ParserFactory::ONLY_PHP7)
            ->parse('<?php ' . $body);
    }

    /**
     * @param ClassMethod $method
     *
     * @return void
     */
    private function replaceExtract(ClassMethod $method)
    {
        $parser = (new ParserFactory)->create(ParserFactory::ONLY_PHP7);

        $method->params = [new Param('object')];

        if (! $this->accessibleProperties && ! $this->propertyWriters) {
            // no properties to hydrate

            $method->stmts = $parser->parse('<?php return array();');

            return;
        }

        $body = '';

        if ($this->propertyWriters) {
            $body = "\$data = (array) \$object;\n\n";
        }

        $body .= 'return array(';

        foreach ($this->accessibleProperties as $accessibleProperty) {
            if (! $this->propertyWriters || ! $accessibleProperty->isProtected()) {
                $body .= "\n    "
                    . var_export($accessibleProperty->getName(), true)
                    . ' => $object->' . $accessibleProperty->getName() . ',';
            } else {
                $body .= "\n    "
                    . var_export($accessibleProperty->getName(), true)
                    . ' => $data["\\0*\\0' . $accessibleProperty->getName() . '"],';
            }
        }

        foreach ($this->propertyWriters as $propertyWriter) {
            $property     = $propertyWriter->getOriginalProperty();
            $propertyName = $property->getName();

            $body .= "\n    "
                . var_export($propertyName, true)
                . ' => $data["'
                . '\\0' . $property->getDeclaringClass()->getName()
                . '\\0' . $propertyName
                . '"],';
        }

        $body .= "\n);";

        $method->stmts = $parser->parse('<?php ' . $body);
    }

    /**
     * Finds or creates a class method (and eventually attaches it to the class itself)
     *
     * @param Class_ $class
     * @param string                    $name  name of the method
     *
     * @return ClassMethod
     */
    private function findOrCreateMethod(Class_ $class, string $name) : ClassMethod
    {
        $foundMethods = array_filter(
            $class->getMethods(),
            function (ClassMethod $method) use ($name) : bool {
                return $name === $method->name;
            }
        );

        $method = reset($foundMethods);

        if (!$method) {
            $class->stmts[] = $method = new ClassMethod($name);
        }

        return $method;
    }

    /**
     * Retrieve instance public/protected properties
     *
     * @param ReflectionClass $reflectedClass
     *
     * @return ReflectionProperty[]
     */
    private function getProtectedProperties(ReflectionClass $reflectedClass) : array
    {
        return array_filter(
            $reflectedClass->getProperties(),
            function (ReflectionProperty $property) : bool {
                return ($property->isPublic() || $property->isProtected()) && ! $property->isStatic();
            }
        );
    }

    /**
     * Retrieve instance private properties
     *
     * @param ReflectionClass $reflectedClass
     *
     * @return ReflectionProperty[]
     */
    private function getPrivateProperties(ReflectionClass $reflectedClass) : array
    {
        return array_filter(
            $reflectedClass->getProperties(),
            function (ReflectionProperty $property) : bool {
                return $property->isPrivate() && ! $property->isStatic();
            }
        );
    }
}
