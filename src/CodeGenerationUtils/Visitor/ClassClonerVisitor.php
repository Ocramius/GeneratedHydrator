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

use PHPParser_Lexer_Emulative;
use PHPParser_Node;
use PHPParser_NodeVisitorAbstract;
use PHPParser_Parser;
use ReflectionClass;

/**
 * Visitor capable of generating an AST given a reflection class that is written in a file
 *
 * @todo doesn't work with evaluated code (file must exist)
 * @todo simply skips if the AST is not empty - should instead be extended to decide what to do
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
class ClassClonerVisitor extends PHPParser_NodeVisitorAbstract
{
    /**
     * @var ReflectionClass
     */
    private $reflectedClass;

    /**
     * @param ReflectionClass $reflectedClass
     */
    public function __construct(ReflectionClass $reflectedClass)
    {
        $this->reflectedClass = $reflectedClass;

    }

    /**
     * {@inheritDoc}
     *
     * @param array $nodes
     *
     * @return \PHPParser_Node[]
     */
    public function beforeTraverse(array $nodes)
    {
        // quickfix - if the list is empty, replace it it
        if (empty($nodes)) {
            $parser = new PHPParser_Parser(new PHPParser_Lexer_Emulative);

            return $parser->parse(file_get_contents($this->reflectedClass->getFileName()));
        }

        return $nodes;
    }
}
