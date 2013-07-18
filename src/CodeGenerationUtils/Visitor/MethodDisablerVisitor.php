<?php

namespace CodeGenerationUtils\Visitor;

use PHPParser_Node;
use PHPParser_Node_Expr_New;
use PHPParser_Node_Name_FullyQualified;
use PHPParser_Node_Stmt_Throw;
use PHPParser_NodeVisitorAbstract;

class MethodDisablerVisitor extends PHPParser_NodeVisitorAbstract
{
    private $filter;
    public function __construct(callable $filter)
    {
        $this->filter = $filter;
    }

    public function leaveNode(PHPParser_Node $node)
    {
        $filter = $this->filter;

        if (! $node instanceof \PHPParser_Node_Stmt_ClassMethod || null === ($filterResult = $filter($node))) {
            return null;
        }

        if (false === $filterResult) {
            return false;
        }

        $node->stmts = array(
            new PHPParser_Node_Stmt_Throw(
                new PHPParser_Node_Expr_New(
                    new PHPParser_Node_Name_FullyQualified('BadMethodCallException'),
                    array(new \PHPParser_Node_Arg(new \PHPParser_Node_Scalar_String('Method is disabled')))
                )
            )
        );

        return $node;
    }
}
