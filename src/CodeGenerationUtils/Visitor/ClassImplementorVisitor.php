<?php

namespace CodeGenerationUtils\Visitor;

use PHPParser_Node;
use PHPParser_Node_Name_FullyQualified;
use PHPParser_Node_Stmt_Class;
use PHPParser_Node_Stmt_Namespace;
use PHPParser_NodeVisitorAbstract;

class ClassImplementorVisitor extends PHPParser_NodeVisitorAbstract
{
    private $matchedClassFQCN;

    /**
     * @var \PHPParser_Node_Name[]
     */
    private $implementedInterfaces;
    private $currentNamespace;

    /**
     * @param string   $matchedClassFQCN
     * @param string[] $implementedInterfaces
     *
     * @todo reduce code duplication with the extension visitor and the class renamer visitor
     */
    public function __construct($matchedClassFQCN, array $implementedInterfaces)
    {
        $this->matchedClassFQCN      = (string) $matchedClassFQCN;
        $this->implementedInterfaces = array_map(
            function ($interfaceName) {
                return new PHPParser_Node_Name_FullyQualified($interfaceName);
            },
            $implementedInterfaces
        );
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

    // @todo this logic is basically a transformation applied on a filtered node
    // @todo it can be abstracted away into a visitor that allows to modify the node via a callback
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
                $node->implements = $this->implementedInterfaces;
            }

            return $node;
        }
    }
}
