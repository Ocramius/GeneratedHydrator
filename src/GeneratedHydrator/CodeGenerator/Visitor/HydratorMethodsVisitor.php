<?php

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
use function array_filter;
use function array_merge;
use function array_values;
use function implode;
use function reset;
use function var_export;

/**
 * Replaces methods `__construct`, `hydrate` and `extract` in the classes of the given AST
 *
 * @todo as per https://github.com/Ocramius/GeneratedHydrator/pull/59, using a visitor for this is ineffective.
 * @todo Instead, we can just create a code generator, since we are not modifying code, but creating it.
 */
class HydratorMethodsVisitor extends NodeVisitorAbstract
{
    /** @var string[] */
    private $visiblePropertyMap = [];

    /** @var string[][] */
    private $hiddenPropertyMap = [];

    public function __construct(ReflectionClass $reflectedClass)
    {
        foreach ($this->findAllInstanceProperties($reflectedClass) as $property) {
            $className = $property->getDeclaringClass()->getName();

            if ($property->isPrivate() || $property->isProtected()) {
                $this->hiddenPropertyMap[$className][] = $property->getName();
            } else {
                $this->visiblePropertyMap[] = $property->getName();
            }
        }
    }

    public function leaveNode(Node $node) : ?Class_
    {
        if (! $node instanceof Class_) {
            return null;
        }

        $node->stmts[] = new Property(Class_::MODIFIER_PRIVATE, [
            new PropertyProperty('hydrateCallbacks', new Array_()),
            new PropertyProperty('extractCallbacks', new Array_()),
        ]);

        $this->replaceConstructor($this->findOrCreateMethod($node, '__construct'));
        $this->replaceHydrate($this->findOrCreateMethod($node, 'hydrate'));
        $this->replaceExtract($this->findOrCreateMethod($node, 'extract'));

        return $node;
    }

    /**
     * Find all class properties recursively using class hierarchy without
     * removing name redefinitions
     *
     * @return \ReflectionProperty[]
     */
    private function findAllInstanceProperties(?\ReflectionClass $class = null) : array
    {
        if (! $class) {
            return [];
        }

        return array_values(array_merge(
            $this->findAllInstanceProperties($class->getParentClass() ?: null), // of course PHP is shit.
            array_values(array_filter(
                $class->getProperties(),
                function (\ReflectionProperty $property) : bool {
                    return ! $property->isStatic();
                }
            ))
        ));
    }

    private function replaceConstructor(ClassMethod $method) : void
    {
        $method->params = [];

        $bodyParts = [];

        // Create a set of closures that will be called to hydrate the object.
        // Array of closures in a naturally indexed array, ordered, which will
        // then be called in order in the hydrate() and extract() methods.
        foreach ($this->hiddenPropertyMap as $className => $propertyNames) {
            // Hydrate closures
            $bodyParts[] = '$this->hydrateCallbacks[] = \\Closure::bind(function ($object, $values) {';
            foreach ($propertyNames as $propertyName) {
                $bodyParts[] = "    if (isset(\$values['" . $propertyName . "']) || " .
                '$object->' . $propertyName . " !== null && \\array_key_exists('" . $propertyName . "', \$values)) {";
                $bodyParts[] = '        $object->' . $propertyName . " = \$values['" . $propertyName . "'];";
                $bodyParts[] = '    }';
            }
            $bodyParts[] = '}, null, ' . var_export($className, true) . ');' . "\n";

            // Extract closures
            $bodyParts[] = '$this->extractCallbacks[] = \\Closure::bind(function ($object, &$values) {';
            foreach ($propertyNames as $propertyName) {
                $bodyParts[] = "    \$values['" . $propertyName . "'] = \$object->" . $propertyName . ';';
            }
            $bodyParts[] = '}, null, ' . var_export($className, true) . ');' . "\n";
        }

        $method->stmts = (new ParserFactory())
            ->create(ParserFactory::ONLY_PHP7)
            ->parse('<?php ' . implode("\n", $bodyParts));
    }

    private function replaceHydrate(ClassMethod $method) : void
    {
        $method->params = [
            new Param('data', null, 'array'),
            new Param('object'),
        ];

        $bodyParts = [];
        foreach ($this->visiblePropertyMap as $propertyName) {
            $bodyParts[] = "if (isset(\$data['" . $propertyName . "']) || " .
            '$object->' . $propertyName . " !== null && \\array_key_exists('" . $propertyName . "', \$data)) {";
            $bodyParts[] = '    $object->' . $propertyName . " = \$data['" . $propertyName . "'];";
            $bodyParts[] = '}';
        }
        $index = 0;
        foreach ($this->hiddenPropertyMap as $className => $propertyNames) {
            $bodyParts[] = '$this->hydrateCallbacks[' . ($index++) . ']->__invoke($object, $data);';
        }

        $bodyParts[] = 'return $object;';

        $method->stmts = (new ParserFactory())
            ->create(ParserFactory::ONLY_PHP7)
            ->parse('<?php ' . implode("\n", $bodyParts));
    }

    private function replaceExtract(ClassMethod $method) : void
    {
        $method->params = [new Param('object')];

        $bodyParts   = [];
        $bodyParts[] = '$ret = array();';
        foreach ($this->visiblePropertyMap as $propertyName) {
            $bodyParts[] = "\$ret['" . $propertyName . "'] = \$object->" . $propertyName . ';';
        }
        $index = 0;
        foreach ($this->hiddenPropertyMap as $className => $propertyNames) {
            $bodyParts[] = '$this->extractCallbacks[' . ($index++) . ']->__invoke($object, $ret);';
        }

        $bodyParts[] = 'return $ret;';

        $method->stmts = (new ParserFactory())
            ->create(ParserFactory::ONLY_PHP7)
            ->parse('<?php ' . implode("\n", $bodyParts));
    }

    /**
     * Finds or creates a class method (and eventually attaches it to the class itself)
     *
     * @deprecated not needed if we move away from code replacement
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

        if (! $method) {
            $class->stmts[] = $method = new ClassMethod($name);
        }

        return $method;
    }
}
