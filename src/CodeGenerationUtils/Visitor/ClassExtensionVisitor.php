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
 * Visitor that extends the matched class in the visited AST from another given class
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
class ClassExtensionVisitor extends PHPParser_NodeVisitorAbstract
{
    /**
     * @var string
     */
    private $matchedClassFQCN;

    /**
     * @var string
     */
    private $newParentClassFQCN;

    /**
     * @var \PHPParser_Node_Stmt_Namespace|null
     */
    private $currentNamespace;

    /**
     * @param string $matchedClassFQCN
     * @param string $newParentClassFQCN
     */
    public function __construct($matchedClassFQCN, $newParentClassFQCN)
    {
        $this->matchedClassFQCN = (string) $matchedClassFQCN;
        $this->newParentClassFQCN = (string) $newParentClassFQCN;
    }

    /**
     * {@inheritDoc}
     *
     * Cleans up internal state
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
     * @return PHPParser_Node_Stmt_Namespace|null
     */
    public function enterNode(PHPParser_Node $node)
    {
        if ($node instanceof PHPParser_Node_Stmt_Namespace) {
            $this->currentNamespace = $node;

            return $node;
        }
    }

    /**
     * {@inheritDoc}
     *
     * When leaving a node that is a class, replaces it with a modified version that extends the
     * given parent class
     *
     * @todo can be abstracted away into a visitor that allows to modify the node via a callback
     *
     * @param PHPParser_Node $node
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
                $node->extends = new PHPParser_Node_Name_FullyQualified($this->newParentClassFQCN);
            }

            return $node;
        }
    }
}
