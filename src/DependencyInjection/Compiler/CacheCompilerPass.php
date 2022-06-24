<?php

declare(strict_types=1);

namespace EmagTechLabs\AnnotationCacheBundle\DependencyInjection\Compiler;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\Reader;
use EmagTechLabs\AnnotationCacheBundle\Annotation\Cache;
use EmagTechLabs\AnnotationCacheBundle\ProxyManager\CacheFactory;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\BadMethodCallException;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\Filesystem\Filesystem;

class CacheCompilerPass implements CompilerPassInterface
{
    /**
     * @inheritDoc
     *
     * @return void
     * @throws ReflectionException
     *
     */
    public function process(ContainerBuilder $container)
    {
        $fs = new Filesystem();
        $fs->mkdir(
            $container->getParameterBag()->resolveValue($container->getParameter('annotation_cache.service.path'))
        );

        $this->analyzeServicesTobeCached($container);
    }

    /**
     * @param ContainerBuilder $container
     *
     * @return  void
     * @throws ReflectionException
     */
    private function analyzeServicesTobeCached(ContainerBuilder $container)
    {
        $hasErrors = method_exists(Definition::class, 'hasErrors') ? 'hasErrors' : 'getErrors';

        foreach ($container->getDefinitions() as $id => $definition) {
            if (!$id || $definition->$hasErrors() || $this->isInvalidDefinition($definition, $container)) {
                continue;
            }

            $this->setCachedDefinitions($definition, $container, $id);
        }
    }

    /**
     * @param Definition $definition
     * @param ContainerBuilder $container
     * @return bool
     */
    private function isInvalidDefinition(Definition $definition, ContainerBuilder $container): bool
    {
        try {
            return
                is_null($definition->getClass())
                || !class_exists($definition->getClass())
                || $this->isFromIgnoredNamespace($container, $definition->getClass());
        } catch (\Error $e) {
            return true;
        }
    }

    /**
     * @param ContainerBuilder $container
     * @param string $className
     *
     * @return  bool
     */
    private function isFromIgnoredNamespace(ContainerBuilder $container, string $className): bool
    {
        foreach ($container->getParameter('annotation_cache.ignore.namespaces') as $standardNamespace) {
            if (strpos($className, $standardNamespace) === 0) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param Definition $definition
     * @param ContainerBuilder $container
     * @param mixed $serviceId
     * @throws ReflectionException
     */
    private function setCachedDefinitions(Definition $definition, ContainerBuilder $container, $serviceId): void
    {
        $annotationReader = new AnnotationReader();
        $ignoreNames = $container->getParameter('annotation_cache.ignore.names');
        foreach ($ignoreNames as $ignoreName) {
            AnnotationReader::addGlobalIgnoredName($ignoreName);
        }
        $proxyWarmupDefinition = $container->getDefinition('annotation_cache.warmup');
        /** @var Reader $annotationReaderReference */
        $annotationReaderReference = new Reference("annotation_cache.cache_reader");
        /** @var CacheFactory $cacheProxyFactory */
        $cacheProxyFactory = new Reference('annotation_cache.proxy.factory');
        /** @var ServiceLocator $cacheServiceReference */
        $cacheServiceReference = new Reference('annotation_cache.service.locator');
        $originalReflection = new ReflectionClass($definition->getClass());

        foreach ($originalReflection->getMethods() as $method) {
            if ($annotationReader->getMethodAnnotation($method, Cache::class)) {
                $this->validateMethod($method);

                $wrapper = (new Definition($definition->getClass()))
                    ->setFactory([$cacheProxyFactory, 'generate'])
                    ->setTags($definition->getTags())
                    ->setAutowired($definition->isAutowired())
                    ->setArguments([$definition->getClass(), $definition->getArguments()])
                    ->setMethodCalls($definition->getMethodCalls())
                    ->setProperties($definition->getProperties())
                    ->addMethodCall('setAnnotationCacheReader', [$annotationReaderReference])
                    ->addMethodCall('setServiceLocatorCache', [$cacheServiceReference]);

                $proxyWarmupDefinition->addMethodCall('addClassToGenerate', [$definition->getClass()]);
                $container->setDefinition($serviceId, $wrapper);
                break;
            }
        }
    }

    /**
     * @param ReflectionMethod $method
     */
    private function validateMethod(ReflectionMethod $method): void
    {
        if ($method->isGenerator()) {
            throw new BadMethodCallException('Generator methods can not be cached!');
        }
        if ($method->isFinal()) {
            throw new BadMethodCallException('Final methods can not be cached!');
        }
        if ($method->isAbstract()) {
            throw new BadMethodCallException('Abstract methods can not be cached!');
        }
        if ($method->isStatic()) {
            throw new BadMethodCallException('Static methods can not be cached!');
        }
    }
}
