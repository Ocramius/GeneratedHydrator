<?php

namespace CodeGenerationUtils\Visitor;

use PHPParser_Lexer_Emulative;
use PHPParser_Node;
use PHPParser_Node_Name;
use PHPParser_Node_Stmt_Class;
use PHPParser_Node_Stmt_Namespace;
use PHPParser_NodeVisitorAbstract;
use PHPParser_Parser;
use ReflectionClass;

class ClassRenamerVisitor extends PHPParser_NodeVisitorAbstract
{
    /**
     * @var ReflectionClass
     */
    private $reflectedClass;

    /**
     * @var string
     */
    private $newName;

    /**
     * @var string
     */
    private $newNamespace;

    /**
     * @var PHPParser_Node_Stmt_Namespace|null
     */
    private $currentNamespace;

    /**
     * @var PHPParser_Node_Stmt_Class|null the currently detected class in this namespace
     */
    private $replacedInNamespace;

    /**
     * @param ReflectionClass $reflectedClass
     * @param string          $newFQCN
     */
    public function __construct(ReflectionClass $reflectedClass, $newFQCN)
    {
        $this->reflectedClass = $reflectedClass;
        $fqcnParts            = explode('\\', $newFQCN);
        $this->newNamespace   = implode('\\', array_slice($fqcnParts, 0, -1));
        $this->newName        = end($fqcnParts);
    }

    public function beforeTraverse(array $nodes)
    {
        // reset state
        $this->currentNamespace    = null;
        $this->replacedInNamespace = null;
    }

    public function enterNode(PHPParser_Node $node)
    {
        if ($node instanceof PHPParser_Node_Stmt_Namespace) {
            return $this->currentNamespace = $node;
        }
    }

    public function leaveNode(PHPParser_Node $node)
    {
        if ($node instanceof PHPParser_Node_Stmt_Namespace) {
            $namespace                 = $this->currentNamespace;
            $replacedInNamespace       = $this->replacedInNamespace;
            $this->currentNamespace    = null;
            $this->replacedInNamespace = null;

            if ($namespace && $replacedInNamespace) {
                if (! $this->newNamespace) {
                    // @todo what happens to other classes in here?
                    return array($replacedInNamespace);
                }

                $namespace->name->parts = explode('\\', $this->newNamespace);

                return $namespace;
            }
        }

        if ($node instanceof PHPParser_Node_Stmt_Class
            && $this->namespaceMatches()
            && ($this->reflectedClass->getShortName() === $node->name)
        ) {
            $node->name = $this->newName;

            // @todo too simplistic (assumes single class per namespace right now)
            if ($this->currentNamespace) {
                $this->replacedInNamespace = $node;
            } elseif ($this->newNamespace) {
                // wrap in namespace if no previous namespace exists
                return new PHPParser_Node_Stmt_Namespace(new PHPParser_Node_Name($this->newNamespace), array($node));
            }

            return $node;
        }
    }

    private function namespaceMatches()
    {
        $currentNamespace = ($this->currentNamespace && is_array($this->currentNamespace->name->parts))
            ? implode('\\', $this->currentNamespace->name->parts)
            : '';

        return $currentNamespace === $this->reflectedClass->getNamespaceName();
    }
}
