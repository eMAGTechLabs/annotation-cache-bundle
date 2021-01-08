<?php

namespace Emag\CacheBundle\DependencyInjection;

use Emag\CacheBundle\Annotation\Cache;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * @var string
     */
    private $name;

    /**
     * Configuration constructor.
     *
     * @param   string  $name
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder($this->name);
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->arrayNode('provider')
                    ->useAttributeAsKey('name')
                    ->beforeNormalization()->ifString()->then(function($v) {
                        return [Cache::STORAGE_LABEL_DEFAULT => $v];
                    })->end()
                    ->prototype('scalar')->end()
                ->end()
                ->arrayNode('ignore_namespaces')
                    ->prototype('scalar')->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
