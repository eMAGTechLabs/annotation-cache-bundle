<?php

namespace EmagTechLabs\CacheBundle\DependencyInjection;

use EmagTechLabs\CacheBundle\Exception\CacheException;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class EmagCacheExtension extends Extension implements PrependExtensionInterface
{
    /**
     * @inheritDoc
     */
    public function prepend(ContainerBuilder $container)
    {
        $configuration = new Configuration($this->getAlias());
        $config = $this->processConfiguration($configuration, $container->getExtensionConfig($this->getAlias()));
        foreach ($config['provider'] as $alias => $serviceId) {
            if (!$container->hasDefinition($serviceId)) {
                throw new CacheException(
                    sprintf(
                        'You\'ve referenced a un-existing service of name "%s", please provide another!',
                        $serviceId
                    )
                );
            }
            $provider = new \ReflectionClass($container->getDefinition($serviceId)->getClass());
            if (!$provider->implementsInterface(CacheItemPoolInterface::class)) {
                throw new CacheException(
                    sprintf('You\'ve referenced a service "%s" that can not be used for caching!', $serviceId)
                );
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function getAlias()
    {
        return 'emag_cache';
    }

    /**
     * @inheritdoc
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration($this->getAlias());
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('emag.cache.ignore.namespaces', $config['ignore_namespaces']);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        $locatorDefinition = $container->getDefinition('emag.cache.service.locator');
        $locatorArguments = [];
        foreach ($config['provider'] as $alias => $serviceId) {
            $locatorArguments[$alias] = new Reference($serviceId);
        }
        $locatorDefinition->setArguments(
            [
                $locatorArguments,
            ]
        );
    }
}

