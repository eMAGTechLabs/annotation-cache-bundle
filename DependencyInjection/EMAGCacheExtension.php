<?php

namespace eMAG\CacheBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class EMAGCacheExtension extends Extension
{

    /**
     * @inheritdoc
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Co8nfiguration($this->getAlias());
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('emag.cache.service', $config['provider']);
        $container->setParameter('emag.cache.ignore.namespaces', $config['ignore_namespaces']);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');
    }

    /**
     * @inheritdoc
     */
    public function getAlias()
    {
        return 'emag_cache';
    }
}
