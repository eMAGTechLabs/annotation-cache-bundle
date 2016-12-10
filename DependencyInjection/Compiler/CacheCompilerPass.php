<?php

namespace eMAG\CacheBundle\DependencyInjection;

use eMAG\CacheBundle\Annotation\Cache;
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
        $emagProxyFactory = new Reference('emag.cache.proxy.factory');
        foreach ($this->containerBuilder->getDefinitions() as $serviceId => $definition) {
            if (!class_exists($definition->getClass()) || $this->isFromStandardNamespace($definition->getClass())) {
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
                    ->setFactory([$emagProxyFactory, 'generate'])
                    ->setTags($definition->getTags())
                    ->setArguments([$definition->getClass(), $definition->getArguments()])
                    ->setMethodCalls($definition->getMethodCalls())
                    ->setProperties($definition->getProperties())
                    ->setProperties($definition->getProperties())
                    ->addMethodCall('setReaderForCacheMethod', [$annotationReaderReference])
                    ->addMethodCall('setCacheServiceForMethod', [new Reference($this->containerBuilder->getParameter('emag.cache.service'))])
                ;


                $this->containerBuilder->setDefinition($serviceId, $wrapper);
                break;
            }
        }
    }

    private function isFromStandardNamespace($className)
    {
        foreach ([
        'Symfony\\',
         'Doctrine\\',
         'Twig_',
         'Monolog\\',
         'Swift_',
         'Sensio\\Bundle\\',
         ] as $standardNamespace) {
            if (strpos($className, $standardNamespace) === 0) {
                return true;
            }
        }

        return false;
    }

}