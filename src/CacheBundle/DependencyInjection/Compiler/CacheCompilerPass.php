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
     * You can modify the container here before it is dumped to PHP code.
     *
     * @param ContainerBuilder $container
     *
     * @api
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


        $this->proxyServicesToBeCached($container);
    }

    /**
     * @param ContainerBuilder $container
     * @return array
     */
    protected function proxyServicesToBeCached(ContainerBuilder $container)
    {
        $annotationReader = new AnnotationReader();
        $servicesToBeCached = [];
        foreach ($container->getDefinitions() as $serviceId => $definition) {
            if (!class_exists($definition->getClass())) {
                continue;
            }

            $originalReflection = new \ReflectionClass($definition->getClass());
            foreach ($originalReflection->getMethods() as $method) {
                if ($annotation = $annotationReader->getMethodAnnotation($method, Cache::class)) {
                    if ($method->isFinal()) {
                        throw new BadMethodCallException('Final methods can not be cached!');
                    }
                    if ($method->isAbstract()) {
                        throw new BadMethodCallException('Abstract methods can not be cached!');
                    }
                    if ($method->isStatic()) {
                        throw new BadMethodCallException('Static methods can not be cached!');
                    }

                    $factory = new Definition($definition->getClass());
                    $factory->setFactory([new Reference('emag.cache.proxy.factory'), 'generate']);
                    $factory->setTags($definition->getTags());
                    $factory->setArguments([$definition->getClass(), $definition->getArguments()]);
                    $factory->setMethodCalls($definition->getMethodCalls());
                    $factory->setProperties($definition->getProperties());
                    $factory->setProperties($definition->getProperties());
                    $factory->addMethodCall('setReaderForCacheMethod', [new Reference("annotation_reader")]);
                    $factory->addMethodCall('setCacheServiceForMethod', [new Reference($container->getParameter('cache.service'))]);
                    $container->getDefinition('emag.cache.warmup')->addMethodCall('addClassToGenerate', [$definition->getClass()]);


                    $container->setDefinition($serviceId, $factory);
                    break;
                }
            }
        }

        return $servicesToBeCached;
    }


}