<?php

namespace CodeGenerationUtils\Visitor;

use PHPParser_Node;
use PHPParser_Node_Name_FullyQualified;
use PHPParser_Node_Stmt_Class;
use PHPParser_Node_Stmt_Namespace;
use PHPParser_NodeVisitorAbstract;

class ClassExtensionVisitor extends PHPParser_NodeVisitorAbstract
{
    private $matchedClassFQCN;
    private $newParentClassFQCN;
    private $currentNamespace;

    public function __construct($matchedClassFQCN, $newParentClassFQCN)
    {
        $this->matchedClassFQCN = (string) $matchedClassFQCN;
        $this->newParentClassFQCN = (string) $newParentClassFQCN;
    }

    public function beforeTraverse(array $nodes)
    {
        $this->currentNamespace = null;
    }

    public function enterNode(PHPParser_Node $node)
    {
        if ($node instanceof PHPParser_Node_Stmt_Namespace) {
            $this->currentNamespace = $node;

            return $node;
        }
    }

    public function leaveNode(PHPParser_Node $node)
    {
        if ($node instanceof PHPParser_Node_Stmt_Namespace) {
            $this->currentNamespace = null;
        }

        if ($node instanceof PHPParser_Node_Stmt_Class) {
            $namespace = ($this->currentNamespace && is_array($this->currentNamespace->name->parts))
                ? implode('\\', $this->currentNamespace->name->parts)
                : '';

            if (trim($namespace . '\\' . $node->name, '\\') === $this->matchedClassFQCN) {
                $node->extends = new PHPParser_Node_Name_FullyQualified($this->newParentClassFQCN);
            }

            return $node;
        }
    }
}
