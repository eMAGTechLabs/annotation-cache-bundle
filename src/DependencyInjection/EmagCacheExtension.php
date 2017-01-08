<?php

namespace Emag\CacheBundle\DependencyInjection;

use Emag\CacheBundle\Exception\CacheException;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader;
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
        if (!$container->hasDefinition($config['provider'])) {
            throw new CacheException(sprintf('You\'ve referenced a un-existing service of name "%s", please provide another!', $config['provider']));
        }
        $provider = new \ReflectionClass($container->getDefinition($config['provider'])->getClass());
        if (!$provider->implementsInterface(CacheItemPoolInterface::class)) {
            throw new CacheException(sprintf('You\'ve referenced a service "%s" that can not be used for caching!', $config['provider']));
        }
    }

    /**
     * @inheritdoc
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration($this->getAlias());
        $config = $this->processConfiguration($configuration, $configs);

        $container->setAlias('emag.cache.service', $config['provider']);
        $container->setParameter('emag.cache.ignore.namespaces', $config['ignore_namespaces']);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
    }

    /**
     * @inheritDoc
     */
    public function getAlias()
    {
        return 'emag_cache';
    }
}

