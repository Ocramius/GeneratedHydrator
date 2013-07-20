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

namespace CodeGenerationUtils\ReflectionBuilder;

use PHPParser_Builder_Method;
use PHPParser_Builder_Param;
use PHPParser_Builder_Property;
use PHPParser_BuilderAbstract;
use PHPParser_Node;
use PHPParser_Node_Const;
use PHPParser_Node_Expr_ConstFetch;
use PHPParser_Node_Name;
use PHPParser_Node_Name_FullyQualified;
use PHPParser_Node_Stmt_Class;
use PHPParser_Node_Stmt_ClassConst;
use PHPParser_Node_Stmt_Namespace;
use ReflectionClass;
use ReflectionMethod;
use ReflectionParameter;
use ReflectionProperty;

/**
 * Rudimentary utility to build an AST from a reflection class
 *
 * @todo should be splitted into various utilities like this one and eventually replace `Zend\Code\Generator`
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
class ClassBuilder extends PHPParser_BuilderAbstract
{
    /**
     * @param \ReflectionClass $reflectionClass
     *
     * @return PHPParser_Node[]
     */
    public function fromReflection(ReflectionClass $reflectionClass)
    {
        $class = new PHPParser_Node_Stmt_Class($reflectionClass->getShortName());
        $stmts = array($class);

        if ($parentClass = $reflectionClass->getParentClass()) {
            $class->extends = new PHPParser_Node_Name_FullyQualified($parentClass->getName());
        }

        $interfaces = array();

        foreach ($reflectionClass->getInterfaces() as $reflectionInterface) {
            $interfaces[] = new PHPParser_Node_Name_FullyQualified($reflectionInterface->getName());
        }

        $class->implements = $interfaces;

        foreach ($reflectionClass->getConstants() as $constant => $value) {
            $class->stmts[] = new PHPParser_Node_Stmt_ClassConst(
                array(new PHPParser_Node_Const($constant, $this->normalizeValue($value)))
            );
        }

        foreach ($reflectionClass->getProperties() as $reflectionProperty) {
            $class->stmts[] = $this->buildProperty($reflectionProperty);
        }

        foreach ($reflectionClass->getMethods() as $reflectionMethod) {
            $class->stmts[] = $this->buildMethod($reflectionMethod);
        }

        if (! $namespace = $reflectionClass->getNamespaceName()) {
            return $stmts;
        }

        return array(new PHPParser_Node_Stmt_Namespace(new PHPParser_Node_Name(explode('\\', $namespace)), $stmts));
    }

    /**
     * @throws \BadMethodCallException disabled method
     */
    public function getNode()
    {
        throw new \BadMethodCallException('Disabled');
    }

    /**
     * @param ReflectionProperty $reflectionProperty
     *
     * @return \PHPParser_Node_Stmt_Property
     */
    protected function buildProperty(ReflectionProperty $reflectionProperty)
    {
        $propertyBuilder = new PHPParser_Builder_Property($reflectionProperty->getName());

        if ($reflectionProperty->isPublic()) {
            $propertyBuilder->makePublic();
        }

        if ($reflectionProperty->isProtected()) {
            $propertyBuilder->makeProtected();
        }

        if ($reflectionProperty->isPrivate()) {
            $propertyBuilder->makePrivate();
        }

        if ($reflectionProperty->isStatic()) {
            $propertyBuilder->makeStatic();
        }

        if ($reflectionProperty->isDefault()) {
            $allDefaultProperties = $reflectionProperty->getDeclaringClass()->getDefaultProperties();

            $propertyBuilder->setDefault($allDefaultProperties[$reflectionProperty->getName()]);
        }

        return $propertyBuilder->getNode();
    }

    /**
     * @param ReflectionMethod $reflectionMethod
     *
     * @return \PHPParser_Node_Stmt_ClassMethod
     */
    protected function buildMethod(ReflectionMethod $reflectionMethod)
    {
        $methodBuilder = new PHPParser_Builder_Method($reflectionMethod->getName());

        if ($reflectionMethod->isPublic()) {
            $methodBuilder->makePublic();
        }

        if ($reflectionMethod->isProtected()) {
            $methodBuilder->makeProtected();
        }

        if ($reflectionMethod->isPrivate()) {
            $methodBuilder->makePrivate();
        }

        if ($reflectionMethod->isStatic()) {
            $methodBuilder->makeStatic();
        }

        if ($reflectionMethod->isAbstract()) {
            $methodBuilder->makeAbstract();
        }

        if ($reflectionMethod->isFinal()) {
            $methodBuilder->makeFinal();
        }

        if ($reflectionMethod->returnsReference()) {
            $methodBuilder->makeReturnByRef();
        }

        foreach ($reflectionMethod->getParameters() as $reflectionParameter) {
            $methodBuilder->addParam($this->buildParameter($reflectionParameter));
        }

        // @todo should parse method body if possible (skipped for now)

        return $methodBuilder->getNode();
    }

    /**
     * @param ReflectionParameter $reflectionParameter
     *
     * @return \PHPParser_Node_Param
     */
    protected function buildParameter(ReflectionParameter $reflectionParameter)
    {
        $parameterBuilder = new PHPParser_Builder_Param($reflectionParameter->getName());

        if ($reflectionParameter->isPassedByReference()) {
            $parameterBuilder->makeByRef();
        }

        if ($reflectionParameter->isArray()) {
            $parameterBuilder->setTypeHint('array');
        }

        if (method_exists($reflectionParameter, 'isCallable') && $reflectionParameter->isCallable()) {
            $parameterBuilder->setTypeHint('callable');
        }

        if ($type = $reflectionParameter->getClass()) {
            $parameterBuilder->setTypeHint($type->getName());
        }

        if ($reflectionParameter->isDefaultValueAvailable()) {
            if ($reflectionParameter->isDefaultValueConstant()) {
                $parameterBuilder->setDefault(
                    new PHPParser_Node_Expr_ConstFetch($reflectionParameter->getDefaultValueConstantName())
                );
            } else {
                $parameterBuilder->setDefault($reflectionParameter->getDefaultValue());
            }
        }

        return $parameterBuilder->getNode();
    }
}
