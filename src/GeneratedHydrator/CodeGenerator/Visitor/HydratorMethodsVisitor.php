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

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\PropertyProperty;
use PhpParser\NodeVisitorAbstract;
use PhpParser\ParserFactory;
use ReflectionClass;
use ReflectionProperty;

/**
 * Replaces methods `__construct`, `hydrate` and `extract` in the classes of the given AST
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @author Pierre Rineau <pierre.rineau@processus.org>
 * @license MIT
 */
class HydratorMethodsVisitor extends NodeVisitorAbstract
{
    /**
     * @var ReflectionClass
     */
    private $reflectedClass;

    /**
     * @var string[][]
     */
    private $classPropertyMap = array();

    /**
     * @param ReflectionClass $reflectedClass
     */
    public function __construct(ReflectionClass $reflectedClass)
    {
        $this->reflectedClass = $reflectedClass;

        foreach ($this->recursiveFindNonStaticProperties($reflectedClass) as $property) {
            $className = $property->getDeclaringClass()->getName();
            $this->classPropertyMap[$className][] = $property->getName();
        }
    }

    /**
     * @param Node $node
     *
     * @return null|Class_
     */
    public function leaveNode(Node $node)
    {
        if (!$node instanceof Class_) {
            return null;
        }

        $node->stmts[] = new Property(Class_::MODIFIER_PRIVATE, array(
            new PropertyProperty('hydrateCallbacks', new Array_()),
            new PropertyProperty('extractCallbacks', new Array_()),
        ));

        $this->replaceConstructor($this->findOrCreateMethod($node, '__construct'));
        $this->replaceHydrate($this->findOrCreateMethod($node, 'hydrate'));
        $this->replaceExtract($this->findOrCreateMethod($node, 'extract'));

        return $node;
    }

    /**
     * Find all class properties recursively using class hierarchy without
     * removing name redefinitions
     *
     * @param \ReflectionClass $class
     *
     * @return \ReflectionProperty[]
     */
    private function recursiveFindNonStaticProperties(\ReflectionClass $class)
    {
        $ret = [];

        if ($parentClass = $class->getParentClass()) {
            $ret = $this->recursiveFindProperties($parentClass);
        }

        // We cannot filter with NOT static
        foreach ($class->getProperties() as $property) {
            if (!$property->isStatic()) {
                $ret[] = $property;
            }
        }

        return $ret;
    }

    /**
     * @param ClassMethod $method
     */
    private function replaceConstructor(ClassMethod $method)
    {
        $method->params = array();

        $bodyParts = array();

        foreach ($this->classPropertyMap as $className => $propertyNames) {
            // Hydrate closures
            $bodyParts[] = "\$this->hydrateCallbacks[] = \\Closure::bind(function (\$object, \$values) {";
            foreach ($propertyNames as $propertyName) {
                $bodyParts[] = "    if (isset(\$values['" . $propertyName . "'])) {";
                $bodyParts[] = "        \$object->" . $propertyName . " = \$values['" . $propertyName . "'];";
                $bodyParts[] = "    }";
            }
            $bodyParts[] = '}, null, ' . var_export($className, true) . ');' . "\n";

            // Extract closures
            $bodyParts[] = "\$this->extractCallbacks[] = \\Closure::bind(function (\$object, &\$values) {";
            foreach ($propertyNames as $propertyName) {
                $bodyParts[] = "    \$values['" . $propertyName . "'] = \$object->" . $propertyName . ";";
            }
            $bodyParts[] = '}, null, ' . var_export($className, true) . ');' . "\n";
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
        $method->params = array(
            new Param('data', null, 'array'),
            new Param('object'),
        );

        $body = <<<EOT
foreach (\$this->hydrateCallbacks as \$callback) {
    \$callback->__invoke(\$object, \$data);
}
return \$object;
EOT;

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
        $method->params = array(new Param('object'));

        $body = <<<EOT
\$ret = [];
foreach (\$this->extractCallbacks as \$callback) {
    \$callback->__invoke(\$object, \$ret);
}
return \$ret;
EOT;

        $method->stmts = (new ParserFactory())
            ->create(ParserFactory::ONLY_PHP7)
            ->parse('<?php ' . $body);
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
}
