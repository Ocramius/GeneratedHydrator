<?php

namespace GeneratedHydrator\ClassGenerator;

use ProxyManager\Generator\ClassGenerator;
use ProxyManager\Generator\MethodGenerator;
use Zend\Code\Generator\PropertyGenerator;
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

        $builder->addStmts($this->generateProperties());
        $builder->addStmts($this->generateMethods());



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

    private function generateProperties()
    {
        $properties = array();

        foreach ($this->getProperties() as $property) {
            if ($property->isConst()) {
                // @todo this looks like a missing feature of PHPParser
                $tempBuilder     = new \PHPParser_Builder_Property($property->getName());

                $tempBuilder->setDefault($property->getDefaultValue());

                /* @var $tempConst \PHPParser_Node_Stmt_PropertyProperty */
                $tempConst = $tempBuilder->getNode();

                // property "default" is discovered via __get
                $properties[] = new \PHPParser_Node_Const($property->getName(), $tempConst->default);

                continue;
            }

            $propertyBuilder = new \PHPParser_Builder_Property($property->getName());

            if ($property->isStatic()) {
                $propertyBuilder->makeStatic();
            }

            if (PropertyGenerator::FLAG_PUBLIC & $property->getVisibility()) {
                $propertyBuilder->makePublic();
            }

            if (PropertyGenerator::FLAG_PROTECTED & $property->getVisibility()) {
                $propertyBuilder->makeProtected();
            }

            if (PropertyGenerator::FLAG_PRIVATE & $property->getVisibility()) {
                $propertyBuilder->makePrivate();
            }

            if ($defaultValue = $property->getDefaultValue()) {
                $propertyBuilder->setDefault($defaultValue->getValue());
            }

            $properties[] = $propertyBuilder->getNode();
        }

        return $properties;
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