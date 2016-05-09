<?php

namespace CacheBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class CacheExtension extends Extension
{

    /**
     * @inheritdoc
     */
    public function load(array $config, ContainerBuilder $container)
    {
        // TODO: Implement load() method.
    }
}
