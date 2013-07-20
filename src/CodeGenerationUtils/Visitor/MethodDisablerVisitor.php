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
use PHPParser_Node_Expr_New;
use PHPParser_Node_Name_FullyQualified;
use PHPParser_Node_Stmt_ClassMethod;
use PHPParser_Node_Stmt_Throw;
use PHPParser_NodeVisitorAbstract;

/**
 * Disables class methods matching a given filter by replacing their body so that
 * they throw an exception when they are called.
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
class MethodDisablerVisitor extends PHPParser_NodeVisitorAbstract
{
    /**
     * @var callable
     */
    private $filter;

    /**
     * Constructor.
     *
     * @param callable $filter a filter method that accepts a single parameter of
     *                         type {@see \PHPParser_Node} and returns null|true|false to
     *                         respectively ignore, remove or replace it.
     */
    public function __construct(callable $filter)
    {
        $this->filter = $filter;
    }

    /**
     * Replaces the given node if it is a class method and matches according to the given callback
     *
     * @param PHPParser_Node $node
     *
     * @return bool|null|PHPParser_Node_Stmt_ClassMethod
     */
    public function leaveNode(PHPParser_Node $node)
    {
        $filter = $this->filter;

        if (! $node instanceof PHPParser_Node_Stmt_ClassMethod || null === ($filterResult = $filter($node))) {
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
