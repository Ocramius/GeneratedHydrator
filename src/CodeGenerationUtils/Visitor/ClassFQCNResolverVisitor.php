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

use CodeGenerationUtils\Visitor\Exception\UnexpectedValueException;
use PHPParser_Node;
use PHPParser_Node_Stmt_Class;
use PHPParser_Node_Stmt_Namespace;
use PHPParser_NodeVisitorAbstract;
use PHPParser_Parser;

/**
 * Resolves the FQCN of the class included in the AST.
 * Assumes a single class.
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
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

    /**
     * {@inheritDoc}
     *
     * @param array $nodes
     */
    public function beforeTraverse(array $nodes)
    {
        $this->namespace = null;
        $this->class     = null;
    }

    /**
     * @param PHPParser_Node $node
     *
     * @throws Exception\UnexpectedValueException if more than one class is found
     */
    public function enterNode(PHPParser_Node $node)
    {
        if ($node instanceof PHPParser_Node_Stmt_Namespace) {
            if ($this->namespace) {
                throw new UnexpectedValueException('Multiple nested namespaces discovered (invalid AST?)');
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
     * @return string the short name of the discovered class
     *
     * @throws Exception\UnexpectedValueException if no class could be resolved
     */
    public function getName()
    {
        if (! $this->class) {
            throw new UnexpectedValueException('No class discovered');
        }

        return $this->class->name;
    }

    /**
     * @return string the namespace name of the discovered class
     */
    public function getNamespace()
    {
        return $this->namespace ? $this->namespace->name->toString() : '';
    }
}
