<?php

namespace CacheBundle\DependencyInjection\Compiler;


use CacheBundle\Annotation\Cache;
use Doctrine\Common\Annotations\AnnotationReader;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\BadMethodCallException;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Filesystem\Filesystem;

class CacheCompilerPass implements CompilerPassInterface
{
    /**
     * @inheritDoc
     */
    public function process(ContainerBuilder $container)
    {
        $fs = new Filesystem();
        $fs->mkdir(
            str_replace(
                '%kernel.cache_dir%',
                $container->getParameter('kernel.cache_dir'),
                $container->getParameter('emag.cacheable.service.path')
            )
        );

        $this->analyzeServicesTobeCached($container);
    }

    /**
     * @param   ContainerBuilder $container
     *
     * @return  void
     */
    protected function analyzeServicesTobeCached(ContainerBuilder $container)
    {
        $annotationReader = new AnnotationReader();
        $annotationReaderReference = new Reference("annotation_reader");
        $proxyWarmup = $container->getDefinition('emag.cache.warmup');
        $cacheProxyFactory = new Reference('emag.cache.proxy.factory');
        $cacheServiceReference = new Reference($container->getParameter('emag.cache.service'));

        foreach ($container->getDefinitions() as $serviceId => $definition) {
            if (!class_exists($definition->getClass()) || $this->isFromIgnoredNamespace($container, $definition->getClass())) {
                continue;
            }

            $originalReflection = new \ReflectionClass($definition->getClass());
            foreach ($originalReflection->getMethods() as $method) {
                if ($annotationReader->getMethodAnnotation($method, Cache::class)) {
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

                    $wrapper = new Definition($definition->getClass());
                    $wrapper
                        ->setFactory([$cacheProxyFactory, 'generate'])
                        ->setTags($definition->getTags())
                        ->setArguments([$definition->getClass(), $definition->getArguments()])
                        ->setMethodCalls($definition->getMethodCalls())
                        ->setProperties($definition->getProperties())
                        ->setProperties($definition->getProperties())
                        ->addMethodCall('setReaderForCacheMethod', [$annotationReaderReference])
                        ->addMethodCall('setCacheServiceForMethod', [$cacheServiceReference])
                    ;

                    $proxyWarmup->addMethodCall('addClassToGenerate', [$definition->getClass()]);

                    $container->setDefinition($serviceId, $wrapper);
                    break;
                }
            }
        }
    }

    /**
     * @param   string  $className
     *
     * @return  bool
     */
    private function isFromIgnoredNamespace(ContainerBuilder $container, $className)
    {
        foreach ($container->getParameter('emag.cache.ignore.namespaces') as $standardNamespace) {
            if (strpos($className, $standardNamespace) === 0) {
                return true;
            }
        }
        return false;
    }


}