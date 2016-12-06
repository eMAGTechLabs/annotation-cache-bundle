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
                new Reference($container->getParameter('cache.service')),
                new Reference('annotation_reader'),
            ]
        );
        $interceptor->addMethodCall(
            'setLogger',
            [
                new Reference('logger'),
            ]
        );
        $interceptor->addTag('monolog.logger', ['channel' => 'cache']);
        $container->setDefinition('cache.interceptor', $interceptor);


        $pointCut = new Definition(
            'CacheBundle\DependencyInjection\PointCut', [
                new Reference('annotation_reader'),
            ]
        );
        $pointCut->addTag('jms_aop.pointcut', ['interceptor' => 'cache.interceptor']);
        $container->setDefinition('cache.pointcut', $pointCut);
    }

}