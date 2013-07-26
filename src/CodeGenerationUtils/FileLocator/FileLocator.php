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

namespace CodeGenerationUtils\FileLocator;

use CodeGenerationUtils\Exception\InvalidGeneratedClassesDirectoryException as InvalidDirectory;

/**
 * {@inheritDoc}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
class FileLocator implements FileLocatorInterface
{
    /**
     * @var string
     */
    protected $generatedClassesDirectory;

    /**
     * @param string $generatedClassesDirectory
     *
     * @throws \CodeGenerationUtils\Exception\InvalidGeneratedClassesDirectoryException
     */
    public function __construct($generatedClassesDirectory)
    {
        $this->generatedClassesDirectory = realpath($generatedClassesDirectory);

        if (false === $this->generatedClassesDirectory) {
            throw InvalidDirectory::generatedClassesDirectoryNotFound($generatedClassesDirectory);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getGeneratedClassFileName($className)
    {
        return $this->generatedClassesDirectory . DIRECTORY_SEPARATOR . str_replace('\\', '', $className) . '.php';
    }
}
