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
    /** @var string[] */
    private $visiblePropertyMap = [];

    /** @var string[][] */
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

    private function generatePropertyHydrateCall(ObjectProperty $property, string $input): array
    {
        $ret = [];

        $propertyName = $property->name;
        $escapedName = \addslashes($propertyName);

        if ($property->type && !$property->required && !$property->hasDefault) {
            $ret[] = "\$object->{$propertyName} = {$input}['{$escapedName}'] ?? null;";
        } else {
            $ret[] = "if (isset(\$values['{$escapedName}']) || \$object->{$propertyName} !== null "
                . "&& \\array_key_exists('{$escapedName}', {$input})) {";
            $ret[] = "    \$object->{$propertyName} = {$input}['{$escapedName}'];";
            $ret[] = '}';
        }

        return $ret;
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
                \assert($property instanceof ObjectProperty);
                $bodyParts = \array_merge($bodyParts, $this->generatePropertyHydrateCall($property, '$values'));
            }
            $bodyParts[] = '}, null, ' . var_export($className, true) . ');' . "\n";

            // Extract closures
            $bodyParts[] = '$this->extractCallbacks[] = \\Closure::bind(static function ($object, &$values) {';
            foreach ($properties as $property) {
                \assert($property instanceof ObjectProperty);
                $propertyName = $property->name;
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
            new Param(new Node\Expr\Variable('data'), null, 'array'),
            new Param(new Node\Expr\Variable('object')),
        ];

        $bodyParts = [];
        foreach ($this->visiblePropertyMap as $property) {
            \assert($property instanceof ObjectProperty);
            $bodyParts = \array_merge($bodyParts, $this->generatePropertyHydrateCall($property, '$data'));
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
            \assert($property instanceof ObjectProperty);
            $propertyName = $property->name;
            $bodyParts[] = "\$ret['" . $propertyName . "'] = \$object->" . $propertyName . ';';
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

/**
 * @internal
 */
final class ObjectProperty
{
    /** @var ?string */
    public $type = null;

    /** @var bool */
    public $hasDefault = false;

    /** @var ?string */
    public $required = false;

    /** @var string  */
    public $name;

    private function __construct(string $name, ?string $type = null, bool $required = false, bool $hasDefault = false)
    {
        $this->name = $name;
        $this->type = $type;
        $this->required = $required;
        $this->hasDefault = $hasDefault;
    }

    /**
     * Create instance from reflection object
     */
    public static function fromReflection(\ReflectionProperty $property)
    {
        $propertyName = $property->getName();

        if (0 <= \version_compare(PHP_VERSION, '7.4.0') && ($type = $property->getType())) {
            // Check if property have a default value. It seems there is no
            // other way, it probably will create a confusion between properties
            // defaulting to null and those who will remain unitilialized.
            $defaults = $property->getDeclaringClass()->getDefaultProperties();

            return new self(
                $propertyName,
                $type->getName(),
                !$type->allowsNull(),
                isset($defaults[$propertyName])
            );
        }

        return new self($propertyName);
    }
}
