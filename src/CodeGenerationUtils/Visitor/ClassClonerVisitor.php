<?php

namespace CodeGenerationUtils\Visitor;

use PHPParser_Lexer_Emulative;
use PHPParser_Node;
use PHPParser_NodeVisitorAbstract;
use PHPParser_Parser;
use ReflectionClass;

class ClassClonerVisitor extends PHPParser_NodeVisitorAbstract
{
    /**
     * @var ReflectionClass
     */
    private $reflectedClass;

    /**
     * @param ReflectionClass $reflectedClass
     */
    public function __construct(ReflectionClass $reflectedClass)
    {
        $this->reflectedClass = $reflectedClass;

    }

    public function beforeTraverse(array $nodes)
    {
        // quickfix - if the list is empty, parse it
        if (empty($nodes)) {
            $parser = new PHPParser_Parser(new PHPParser_Lexer_Emulative);

            return $parser->parse(file_get_contents($this->reflectedClass->getFileName()));
        }

        // @todo should instead decide what to do if the AST is not empty - maybe append the class in the end?

        return $nodes;
    }
}