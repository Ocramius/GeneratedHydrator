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
use ReflectionProperty;
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
    /**
     * @var ObjectProperty[]
     * @psalm-var list<ObjectProperty>
     */
    private $visiblePropertyMap = [];

    /**
     * @var array<string, array<int, ObjectProperty>>
     * @psalm-var array<string, list<ObjectProperty>>
     */
    private $hiddenPropertyMap = [];

    public function __construct(ReflectionClass $reflectedClass)
    {
        foreach ($this->findAllInstanceProperties($reflectedClass) as $property) {
            $className = $property->getDeclaringClass()->getName();

            if ($property->isPrivate() || $property->isProtected()) {
                $this->hiddenPropertyMap[$className][] = ObjectProperty::fromReflection($property);
            } else {
                $this->visiblePropertyMap[] = ObjectProperty::fromReflection($property);
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
     * @return ReflectionProperty[]
     */
    private function findAllInstanceProperties(?ReflectionClass $class = null) : array
    {
        if (! $class) {
            return [];
        }

        return array_values(array_merge(
            $this->findAllInstanceProperties($class->getParentClass() ?: null), // of course PHP is shit.
            array_values(array_filter(
                $class->getProperties(),
                static function (ReflectionProperty $property) : bool {
                    return ! $property->isStatic();
                }
            ))
        ));
    }

    /**
     * @return string[]
     *
     * @psalm-return list<string>
     */
    private function generatePropertyHydrateCall(ObjectProperty $property, string $inputArrayName) : array
    {
        $propertyName = $property->name;
        $escapedName  = var_export($propertyName, true);

        if ($property->allowsNull && ! $property->hasDefault) {
            return ['$object->' . $propertyName . ' = ' . $inputArrayName . '[' . $escapedName . '] ?? null;'];
        }

        return [
            'if (isset(' . $inputArrayName . '[' . $escapedName . '])',
            '    || $object->' . $propertyName . ' !== null && \\array_key_exists(' . $escapedName . ', ' . $inputArrayName . ')',
            ') {',
            '    $object->' . $propertyName . ' = ' . $inputArrayName . '[' . $escapedName . '];',
            '}',
        ];
    }

    private function replaceConstructor(ClassMethod $method) : void
    {
        $method->params = [];

        $bodyParts = [];

        // Create a set of closures that will be called to hydrate the object.
        // Array of closures in a naturally indexed array, ordered, which will
        // then be called in order in the hydrate() and extract() methods.
        foreach ($this->hiddenPropertyMap as $className => $properties) {
            // Hydrate closures
            $bodyParts[] = '$this->hydrateCallbacks[] = \\Closure::bind(static function ($object, $values) {';
            foreach ($properties as $property) {
                $bodyParts = array_merge($bodyParts, $this->generatePropertyHydrateCall($property, '$values'));
            }
            $bodyParts[] = '}, null, ' . var_export($className, true) . ');' . "\n";

            // Extract closures
            $bodyParts[] = '$this->extractCallbacks[] = \\Closure::bind(static function ($object, &$values) {';
            foreach ($properties as $property) {
                $propertyName = $property->name;
                if ($property->hasType && !$property->hasDefault) {
                    $escapedName = var_export($propertyName, true);

                    $bodyParts[]  = 'static $pref;';
                    $bodyParts[]  = 'if ($pref === null) {';
                    $bodyParts[] = '    $pref = new \ReflectionProperty($object, ' . $escapedName . ');';
                    $bodyParts[] = '    $pref->setAccessible(true);';
                    $bodyParts[]  = '}';

                    $bodyParts[]  = 'if ($pref->isInitialized($object)) {';
                    $bodyParts[]  = "    \$values['" . $propertyName . "'] = \$object->" . $propertyName . ';';
                    $bodyParts[]  = '}';
                } else {
                    $bodyParts[]  = "    \$values['" . $propertyName . "'] = \$object->" . $propertyName . ';';
                }
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
            new Param(new Node\Expr\Variable('data'), null, 'array'),
            new Param(new Node\Expr\Variable('object')),
        ];

        $bodyParts = [];
        foreach ($this->visiblePropertyMap as $property) {
            $bodyParts = array_merge($bodyParts, $this->generatePropertyHydrateCall($property, '$data'));
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
        $method->params = [new Param(new Node\Expr\Variable('object'))];

        $bodyParts   = [];
        $bodyParts[] = '$ret = array();';
        foreach ($this->visiblePropertyMap as $property) {
            $propertyName = $property->name;
            $bodyParts[]  = "\$ret['" . $propertyName . "'] = \$object->" . $propertyName . ';';
        }
        $index = 0;
        foreach ($this->hiddenPropertyMap as $className => $property) {
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
            static function (ClassMethod $method) use ($name) : bool {
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
