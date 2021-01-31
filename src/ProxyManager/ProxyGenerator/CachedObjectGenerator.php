<?php

declare(strict_types=1);

namespace EmagTechLabs\AnnotationCacheBundle\ProxyManager\ProxyGenerator;

use Doctrine\Common\Annotations\Reader;
use EmagTechLabs\AnnotationCacheBundle\Annotation\Cache;
use EmagTechLabs\AnnotationCacheBundle\ProxyManager\CacheableClassTrait;
use Laminas\Code\Generator\ClassGenerator;
use Laminas\Code\Generator\MethodGenerator as BaseMethodGenerator;
use Laminas\Code\Reflection\MethodReflection;
use ProxyManager\Generator\MethodGenerator;
use ProxyManager\ProxyGenerator\ProxyGeneratorInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;

class CachedObjectGenerator implements ProxyGeneratorInterface
{
    /**
     * @var Reader
     */
    private $annotationReader;

    /**
     * @param Reader $annotationReader
     *
     * @return void
     */
    public function setAnnotationReader(Reader $annotationReader): void
    {
        $this->annotationReader = $annotationReader;
    }

    /**
     * Apply modifications to the provided $classGenerator to proxy logic from $originalClass
     *
     * @param ReflectionClass $originalClass
     * @param ClassGenerator $classGenerator
     *
     * @throws ReflectionException
     */
    public function generate(ReflectionClass $originalClass, ClassGenerator $classGenerator)
    {
        $classGenerator->setExtendedClass($originalClass->getName());
        $classGenerator->addTrait('\\' . CacheableClassTrait::class);
        foreach ($originalClass->getMethods() as $method) {
            $this->generateMethod($method, $classGenerator);
        }
    }

    /**
     * @param ReflectionMethod $method
     * @param ClassGenerator $classGenerator
     * @throws ReflectionException
     */
    private function generateMethod(ReflectionMethod $method, ClassGenerator $classGenerator): void
    {
        $annotation = $this->annotationReader->getMethodAnnotation($method, Cache::class);
        if ($annotation) {
            $body = <<<PHP
        \$ref = new \ReflectionMethod('\\{$method->getDeclaringClass()->getName()}', '{$method->getName()}');
        return \$this->getCached(\$ref, func_get_args());
PHP;
            $newMethod = $this->buildMethod($method);
            $newMethod->setDocBlock("");
            $newMethod->setBody($body);
            $classGenerator->addMethodFromGenerator($newMethod);
        }
    }

    /**
     * @param ReflectionMethod $method
     * @return BaseMethodGenerator
     * @throws ReflectionException
     */
    private function buildMethod(ReflectionMethod $method): BaseMethodGenerator
    {
        return MethodGenerator::fromReflection(
            new MethodReflection(
                $method->getDeclaringClass()->getName(),
                $method->getName()
            )
        );
    }
}
