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

namespace CodeGenerationUtils\GeneratorStrategy;

use PHPParser_PrettyPrinter_Default;
use PHPParser_PrettyPrinterAbstract;

/**
 * Generator strategy that generates the class body
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
class BaseGeneratorStrategy implements GeneratorStrategyInterface
{
    /**
     * @var \PHPParser_PrettyPrinterAbstract|null
     */
    private $prettyPrinter;

    /**
     * {@inheritDoc}
     */
    public function generate(array $ast)
    {
        return $this->getPrettyPrinter()->prettyPrint($ast);
    }

    /**
     * @param PHPParser_PrettyPrinterAbstract $prettyPrinter
     */
    public function setPrettyPrinter(PHPParser_PrettyPrinterAbstract $prettyPrinter)
    {
        $this->prettyPrinter = $prettyPrinter;
    }

    /**
     * @return PHPParser_PrettyPrinterAbstract
     */
    protected function getPrettyPrinter()
    {
        return $this->prettyPrinter ?: $this->prettyPrinter = new PHPParser_PrettyPrinter_Default();
    }
}
