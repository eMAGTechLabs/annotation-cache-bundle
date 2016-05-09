<?php

namespace CacheBundle;


use CacheBundle\Annotation\Cache;
use Doctrine\Common\Annotations\AnnotationReader;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class CacheCompilerPass implements CompilerPassInterface
{
    const CACHE_ANNOTATION_NAME = 'CacheBundle\Annotation\Cache';

    /**
     * You can modify the container here before it is dumped to PHP code.
     *
     * @param ContainerBuilder $container
     *
     * @api
     */
    public function process(ContainerBuilder $container)
    {
        $interceptor = new Definition(
            'CacheBundle\DependencyInjection\Interceptor', [
                new Reference($container->getParameter('tgt.cache.service')),
                new Reference('annotation_reader'),
            ]
        );
        $interceptor->addMethodCall(
            'setLogger',
            [
                new Reference('logger'),
            ]
        );
        $interceptor->addTag('monolog.logger', ['channel' => 'tgt.cache']);
        $container->setDefinition('tgt.cache.interceptor', $interceptor);


        $pointCut = new Definition(
            'CacheBundle\DependencyInjection\PointCut', [
                new Reference('annotation_reader'),
            ]
        );
        $pointCut->addTag('jms_aop.pointcut', ['interceptor' => 'tgt.cache.interceptor']);
        $container->setDefinition('tgt.cache.pointcut', $pointCut);


        $taggedServices = $container->findTaggedServiceIds('targeting.cacheable.service');
        $caching = [];

        foreach ($taggedServices as $id => $tags) {
            $classDefinition = $container->findDefinition($id);
            $className = $classDefinition->getClass();
            $reflexionClass = new \ReflectionClass($className);
            $annotationReader = new AnnotationReader();

            foreach ($reflexionClass->getMethods() as $method) {

                $methodAnnotation = $annotationReader->getMethodAnnotation($method, self::CACHE_ANNOTATION_NAME);
                if ($methodAnnotation) {
                    $caching[$className][$method->getName()]['service_name'] = $id;
                    $caching[$className][$method->getName()]['flags'] = Cache::STATE_ENABLED;

                    foreach ($tags as $tag) {
                        if (isset($tag['reset'])) {
                            $caching[$className][$method->getName()]['flags'] |= Cache::STATE_RESET;
                        }
                    }
                }
            }
        }

        $container->getDefinition('tgt.cache.pointcut')->addMethodCall('setCachedMethods', [$caching]);
        $container->getDefinition('tgt.cache.interceptor')->addMethodCall('setCachedMethods', [$caching]);
    }

}