<?php
namespace CacheBundle\ProxyManager\ProxyGenerator;


use CacheBundle\Annotation\Cache;
use CacheBundle\ProxyManager\CacheableClassTrait;
use Doctrine\Common\Annotations\Reader;
use ProxyManager\ProxyGenerator\ProxyGeneratorInterface;
use ReflectionClass;
use Zend\Code\Generator\ClassGenerator;
use Zend\Code\Generator\MethodGenerator;
use Zend\Code\Reflection\MethodReflection;

class CachedObjectGenerator implements ProxyGeneratorInterface
{

    /** @var  Reader */
    protected $annotationReader;

    /**
     * Apply modifications to the provided $classGenerator to proxy logic from $originalClass
     *
     * @param \ReflectionClass $originalClass
     * @param \Zend\Code\Generator\ClassGenerator $classGenerator
     *
     * @return void
     */
    public function generate(ReflectionClass $originalClass, ClassGenerator $classGenerator)
    {
        $classGenerator->setExtendedClass($originalClass->getName());
        $classGenerator->addTrait('\\' . CacheableClassTrait::class);
        foreach ($originalClass->getMethods() as $method)
        {
            $annotation = $this->annotationReader->getMethodAnnotation($method, Cache::class);
            if ($annotation) {
                $parameters = [];
                foreach ($method->getParameters() as $parameter) {
                    $parameters[] = "$".$parameter->getName();
                }
                $body = <<<PHP
        \$ref = new \ReflectionMethod('\\{$method->getDeclaringClass()->getName()}', '{$method->getName()}');
        return \$this->getCached(\$ref, func_get_args());
PHP;
                $newm = MethodGenerator::fromReflection(
                    new MethodReflection(
                        $method->getDeclaringClass()->getName(),
                        $method->getName()
                    )
                );
                $newm->setDocBlock("");
                $newm->setBody($body);
                $classGenerator->addMethodFromGenerator($newm);
            }
        }
    }

    /**
     * @param Reader $annotationReader
     */
    public function setAnnotationReader(Reader $annotationReader)
    {
        $this->annotationReader = $annotationReader;
    }
}