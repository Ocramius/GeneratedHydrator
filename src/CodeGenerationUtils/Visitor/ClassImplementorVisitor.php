<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license.
 */

namespace CodeGenerationUtils\Visitor;

use PHPParser_Node;
use PHPParser_Node_Name_FullyQualified;
use PHPParser_Node_Stmt_Class;
use PHPParser_Node_Stmt_Namespace;
use PHPParser_NodeVisitorAbstract;

/**
 * Implements the given interfaces on the given class name within the AST
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
class ClassImplementorVisitor extends PHPParser_NodeVisitorAbstract
{
    private $matchedClassFQCN;

    /**
     * @var \PHPParser_Node_Name[]
     */
    private $interfaces;

    /**
     * @var PHPParser_Node_Stmt_Namespace|null
     */
    private $currentNamespace;

    /**
     * @param string   $matchedClassFQCN
     * @param string[] $interfaces
     */
    public function __construct($matchedClassFQCN, array $interfaces)
    {
        $this->matchedClassFQCN = (string) $matchedClassFQCN;
        $this->interfaces       = array_map(
            function ($interfaceName) {
                return new PHPParser_Node_Name_FullyQualified($interfaceName);
            },
            $interfaces
        );
    }

    /**
     * Cleanup internal state
     *
     * @param array $nodes
     */
    public function beforeTraverse(array $nodes)
    {
        $this->currentNamespace = null;
    }

    /**
     * @param PHPParser_Node $node
     *
     * @return PHPParser_Node_Stmt_Namespace|void
     */
    public function enterNode(PHPParser_Node $node)
    {
        if ($node instanceof PHPParser_Node_Stmt_Namespace) {
            $this->currentNamespace = $node;

            return $node;
        }
    }

    /**
     * Replaces class nodes with nodes implementing the given interfaces. Implemented interfaces are replaced,
     * not added.
     *
     * @param PHPParser_Node $node
     *
     * @todo can be abstracted away into a visitor that allows to modify the matched node via a callback
     *
     * @return PHPParser_Node_Stmt_Class|void
     */
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
                $node->implements = $this->interfaces;
            }

            return $node;
        }
    }
}
