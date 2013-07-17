<?php

namespace CodeGenerationUtils\Visitor;

use PHPParser_Node;
use PHPParser_NodeVisitorAbstract;

class PublicMethodsFilterVisitor extends PHPParser_NodeVisitorAbstract
{
    public function leaveNode(PHPParser_Node $node)
    {
        return ($node instanceof \PHPParser_Node_Stmt_ClassMethod && ! $node->isPublic()) ? false : null;
    }
}