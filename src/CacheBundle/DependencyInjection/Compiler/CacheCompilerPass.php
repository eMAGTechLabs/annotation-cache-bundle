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
     * @var ContainerBuilder
     */
    private $containerBuilder;

    /**
     * @inheritDoc
     */
    public function process(ContainerBuilder $container)
    {
        $this->containerBuilder = $container;

        $fs = new Filesystem();
        $fs->mkdir(
            str_replace(
                '%kernel.cache_dir%',
                $container->getParameter('kernel.cache_dir'),
                $container->getParameter('emag.cacheable.service.path')
            )
        );

        $this->analyzeServicesTobeCached();
    }

    /**
     * @return  void
     */
    protected function analyzeServicesTobeCached()
    {
        $annotationReader = new AnnotationReader();
        $annotationReaderReference = new Reference("annotation_reader");
        $proxyWarmup = $this->containerBuilder->getDefinition('emag.cache.warmup');
        $cacheProxyFactory = new Reference('emag.cache.proxy.factory');
        $cacheServiceReference = new Reference($this->containerBuilder->getParameter('emag.cache.service'));

        foreach ($this->containerBuilder->getDefinitions() as $serviceId => $definition) {
            if (!class_exists($definition->getClass()) || $this->isFromIgnoredNamespace($definition->getClass())) {
                continue;
            }

            $originalReflection = new \ReflectionClass($definition->getClass());
            foreach ($originalReflection->getMethods() as $method) {
                if (!$annotationReader->getMethodAnnotation($method, Cache::class)) {
                    continue;
                }

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

                $proxyWarmup->addMethodCall('addClassToGenerate', [$definition->getClass()]);
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
                $this->containerBuilder->setDefinition($serviceId, $wrapper);

                break;
            }
        }
    }
    /**
     * @param   string  $className
     *
     * @return  bool
     */
    private function isFromIgnoredNamespace($className)
    {
        foreach ($this->containerBuilder->getParameter('emag.cache.ignore.namespaces') as $standardNamespace) {
            if (strpos($className, $standardNamespace) === 0) {
                return true;
            }
        }
        return false;
    }
}