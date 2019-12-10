<?php

declare(strict_types=1);

namespace GeneratedHydrator\ClosureGenerator\FileCache;

use GeneratedHydrator\ClosureGenerator\GenerateHydrator;
use PhpParser\BuilderFactory;
use PhpParser\Node;
use PhpParser\PrettyPrinter\Standard;
use ReflectionClass;
use ReflectionException;
use const DIRECTORY_SEPARATOR;
use function file_exists;
use function file_put_contents;
use function str_replace;
use function sys_get_temp_dir;

final class GenerateFileCacheHydrator implements GenerateHydrator
{
    /** @var string */
    private $targetDir;

    /** @var callable[] */
    private static $hydratorsCache = [];

    /** @var ReflectionClass[] */
    private static $classesCache = [];

    public function __construct(?string $targetDir = null)
    {
        $this->targetDir = $targetDir ?? sys_get_temp_dir() . DIRECTORY_SEPARATOR;
    }

    /**
     * @throws ReflectionException
     */
    public function __invoke(string $className) : callable
    {
        if (isset(self::$hydratorsCache[$className])) {
            return self::$hydratorsCache[$className];
        }

        // @todo Use modern PSR folder structure?
        $filename = $this->targetDir . str_replace('\\', '_', $className) . '.php';
        if (! file_exists($filename)) {
            $this->generateFile($className, $filename);
        }
        $generateHydrator = $this;

        $hydrate = require $filename;

        return self::$hydratorsCache[$className] = $hydrate;
    }

    /**
     * @return string[]
     *
     * @throws ReflectionException
     */
    private function getPropertyNames(string $className) : array
    {
        $class         = $this->getClass($className);
        $propertyNames = [];
        foreach ($class->getProperties() as $property) {
            if ($property->isStatic()) {
                continue;
            }
            $propertyNames[] = $property->getName();
        }

        return $propertyNames;
    }

    /**
     * @throws ReflectionException
     */
    private function getClass(string $className) : ReflectionClass
    {
        if (isset(self::$classesCache[$className])) {
            return self::$classesCache[$className];
        }

        $class = new ReflectionClass($className);

//        // fill cache for parents
//        while (($parentClass = $class->getParentClass()) !== false) {
//            self::$classesCache[$parentClass->getName()] = $parentClass;
//        }

        return self::$classesCache[$className] = $class;
    }

    /**
     * @throws ReflectionException
     */
    private function getParentClassName(string $className) : ?string
    {
        $parentClass = $this->getClass($className)->getParentClass();
        if ($parentClass === false) {
            return null;
        }

        return $parentClass->getName();
    }

    /**
     * @throws ReflectionException
     */
    private function generateFile(string $className, string $filename) : void
    {
        $ast = $this->generateAst($className);

        $prettyPrinter = new Standard();
        $code          = $prettyPrinter->prettyPrintFile($ast);
        file_put_contents($filename, $code);
    }

    /**
     * @return Node[]
     *
     * @throws ReflectionException
     */
    private function generateAst(string $className) : array
    {
        $factory         = new BuilderFactory();
        $parentClassName = $this->getParentClassName($className);
        $hasParent       = $parentClassName !== null;

        $ast = [];
        if ($hasParent) {
            $ast[] = new Node\Stmt\Expression(new Node\Expr\Assign(
                $factory->var('hydrateParent'),
                $factory->funcCall(
                    $factory->var('generateHydrator'),
                    [$parentClassName],
                )
            ));
        }

        $closure = new Node\Expr\Closure([
            'static' => true,
            'params' => [
                $factory->param('data')->setType('array')->getNode(),
                $factory->param('object')->setType($className)->getNode(),
            ],
            'uses' => $hasParent ? [$factory->var('hydrateParent')] : [],
            'returnType' => $className,
        ]);
        if ($hasParent) {
            $closure->stmts[] = new Node\Stmt\Expression(
                $factory->funcCall(
                    $factory->var('hydrateParent'),
                    [
                        $factory->var('data'),
                        $factory->var('object'),
                    ]
                )
            );
        }
        foreach ($this->getPropertyNames($className) as $propertyName) {
            $closure->stmts[] =
                new Node\Stmt\If_(
                    $factory->funcCall(
                        '\array_key_exists',
                        $factory->args(
                            [
                                $factory->val($propertyName),
                                $factory->var('data'),
                            ]
                        )
                    ),
                    [
                        'stmts' => [new Node\Stmt\Expression(
                            new Node\Expr\Assign(
                                $factory->propertyFetch($factory->var('object'), $propertyName),
                                new Node\Expr\ArrayDimFetch($factory->var('data'), $factory->val($propertyName))
                            )
                        ),
                        ],
                    ],
                );
        }
        $closure->stmts[] = new Node\Stmt\Return_($factory->var('object'));
        $assign           = new Node\Stmt\Return_(
            $factory->staticCall('\Closure', 'bind', [
                $closure,
                null,
                $className,
            ])
        );
        $ast[]            = $assign;

        return $ast;
    }
}
