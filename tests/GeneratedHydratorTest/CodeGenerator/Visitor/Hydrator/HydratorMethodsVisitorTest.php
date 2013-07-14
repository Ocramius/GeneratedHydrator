<?php

namespace GeneratedHydratorTest\CodeGenerator\Visitor\Hydrator;

use GeneratedHydrator\CodeGenerator\Visitor\ClassClonerVisitor;
use GeneratedHydrator\CodeGenerator\Visitor\ClassRenamerVisitor;
use GeneratedHydrator\CodeGenerator\Visitor\Hydrator\HydratorMethodsVisitor;
use PHPParser_Builder_Class;
use PHPParser_Node_Name;
use PHPParser_Node_Stmt_Class;
use PHPParser_Node_Stmt_Namespace;
use PHPParser_NodeTraverser;
use PHPUnit_Framework_TestCase;
use ReflectionClass;

/**
 * @group CodeGeneration
 */
class HydratorMethodsVisitorTest extends PHPUnit_Framework_TestCase
{
    public function testBasicCodeGeneration()
    {
        eval('class Foo { private $bar; private $baz; protected $tab; protected $tar; public $taw; public $tam; }');

        $parser = new \PHPParser_Parser(new \PHPParser_Lexer());

        $ast = $parser->parse(
            '<?php class Foo { private $bar; private $baz; protected $tab; protected $tar; public $taw; public $tam; }'
        );

        $visitor = new HydratorMethodsVisitor(new ReflectionClass('Foo'));

        $modified = $visitor->enterNode($ast[0]);

        $prettyPrinter = new \PHPParser_PrettyPrinter_Default();

        die($prettyPrinter->prettyPrint(array($modified)));
    }
}
