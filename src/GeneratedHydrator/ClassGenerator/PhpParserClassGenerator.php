<?php

namespace GeneratedHydrator\ClassGenerator;

use ProxyManager\Generator\ClassGenerator;
use ProxyManager\Generator\MethodGenerator;
use Zend\Code\Generator\ValueGenerator;

class PhpParserClassGenerator extends ClassGenerator
{
    public function generate()
    {
        $builder = new \PHPParser_Builder_Class($this->getName());

        foreach ($this->getImplementedInterfaces() as $implementedInterface) {
            $builder->implement($implementedInterface);
        }

        if ($extendedClass = $this->getExtendedClass()) {
            $builder->extend($extendedClass);
        }

        $builder->addStmts($this->generateMethods());

        foreach ($this->getProperties() as $property) {

        }


        $prettyPrinter = new \PHPParser_PrettyPrinter_Default();

        $generated = $prettyPrinter->prettyPrint(
            array(
                 new \PHPParser_Node_Stmt_Namespace(
                     new \PHPParser_Node_Name($this->getNamespaceName()),
                     array($builder->getNode())
                 )
            )
        );

        echo $generated;
        return $generated;
    }

    private function generateMethods()
    {
        $methods = array();

        foreach ($this->getMethods() as $method) {
            $methodBuilder = new \PHPParser_Builder_Method($method->getName());

            if ($method->isFinal()) {
                $methodBuilder->makeFinal();
            }

            if ($method->isStatic()) {
                $methodBuilder->makeStatic();
            }

            // @todo byref method return value
            if (MethodGenerator::FLAG_PUBLIC & $method->getVisibility()) {
                $methodBuilder->makePublic();
            }

            if (MethodGenerator::FLAG_PROTECTED & $method->getVisibility()) {
                $methodBuilder->makeProtected();
            }

            if (MethodGenerator::FLAG_PRIVATE & $method->getVisibility()) {
                $methodBuilder->makePrivate();
            }

            foreach ($method->getParameters() as $parameter) {
                $parameterBuilder = new \PHPParser_Builder_Param($parameter->getName());

                if ($parameter->getPassedByReference()) {
                    $parameterBuilder->makeByRef();
                }

                /* @var $defaultValue ValueGenerator */
                if ($defaultValue = $parameter->getDefaultValue()) {
                    $parameterBuilder->setDefault($defaultValue);
                }

                if ($type = $parameter->getType()) {
                    $parameterBuilder->setTypeHint(new \PHPParser_Node_Name($parameter->getType()));
                }

                $methodBuilder->addParam($parameterBuilder->getNode());
            }

            $methods[] = $methodBuilder->getNode();

        }

        return $methods;
    }
}