<?php

namespace CodeGenerationUtils\Visitor;

use CodeGenerationUtils\Visitor\Exception\UnexpectedValueException;
use PHPParser_Node;
use PHPParser_Node_Stmt_Class;
use PHPParser_Node_Stmt_Namespace;
use PHPParser_NodeVisitorAbstract;
use PHPParser_Parser;

class ClassFQCNResolverVisitor extends PHPParser_NodeVisitorAbstract
{
    /**
     * @var PHPParser_Node_Stmt_Namespace|null
     */
    private $namespace;

    /**
     * @var PHPParser_Node_Stmt_Class|null
     */
    private $class;

    public function beforeTraverse(array $nodes)
    {
        $this->namespace = null;
        $this->class     = null;
    }

    public function enterNode(PHPParser_Node $node)
    {
        if ($node instanceof PHPParser_Node_Stmt_Namespace) {
            if ($this->namespace) {
                throw new UnexpectedValueException('Multiple namespaces discovered');
            }

            $this->namespace = $node;
        }

        if ($node instanceof PHPParser_Node_Stmt_Class) {
            if ($this->class) {
                throw new UnexpectedValueException('Multiple classes discovered');
            }

            $this->class = $node;
        }
    }

    /**
     * @return string
     *
     * @throws Exception\UnexpectedValueException in case no class could be resolved
     */
    public function getFQCN()
    {
        if (! $this->class) {
            throw new UnexpectedValueException('No class discovered');
        }

        return trim(($this->namespace ? $this->namespace->name->toString() : '') . '\\' . $this->class->name, '\\');
    }
}
