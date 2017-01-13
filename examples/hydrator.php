<?php

require_once __DIR__ . '/../vendor/autoload.php';

use GeneratedHydrator\Configuration;

class Foo
{
    private $foo   = 1;
    protected $bar = 2;
    public $baz    = 3;

    public function getFoo()
    {
        return $this->foo;
    }

    public function getBar()
    {
        return $this->bar;
    }

    public function getBaz()
    {
        return $this->baz;
    }
}

$config        = new Configuration('Foo');
$hydratorClass = $config->createFactory()->getHydratorClass();
$hydrator      = new $hydratorClass();
$foo           = new Foo();

$data = $hydrator->extract($foo);

echo "\nExtracted data:\n";
echo 'foo: ' . $data['foo'] . "\n"; // 1
echo 'bar: ' . $data['bar'] . "\n"; // 2
echo 'baz: ' . $data['baz'] . "\n"; // 3

$hydrator->hydrate(
    [
         'foo' => 4,
         'bar' => 5,
         'baz' => 6,
    ],
    $foo
);

echo "\nObject hydrated with new data:\n";
echo 'foo: ' . $foo->getFoo() . "\n"; // 4
echo 'bar: ' . $foo->getBar() . "\n"; // 5
echo 'baz: ' . $foo->getBaz() . "\n"; // 6
