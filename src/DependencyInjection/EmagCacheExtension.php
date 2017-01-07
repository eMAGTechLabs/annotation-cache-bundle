<?php

namespace Emag\CacheBundle\DependencyInjection;

use Emag\CacheBundle\Exception\CacheException;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
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

        if (!$config['expression_language']) {
            return;
        }

        if (!class_exists('Symfony\Component\ExpressionLanguage\ExpressionLanguage')) {
            throw new CacheException('Unable to use expressions as the Symfony ExpressionLanguage component is not installed.');
        }

        $expressionLanguage = new \ReflectionClass($container->getDefinition($config['expression_language'])->getClass());
        if ($expressionLanguage->getName() !== ExpressionLanguage::class) {
            throw new CacheException(sprintf('You must provide a valid Expression Language service'));
        }
    }

    /**
     * @inheritdoc
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration($this->getAlias());
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('emag.cache.service', $config['provider']);
        $container->setParameter('emag.cache.ignore.namespaces', $config['ignore_namespaces']);
        if (!$config['expression_language'] && class_exists('Symfony\Component\ExpressionLanguage\ExpressionLanguage')) {
            $container->addDefinitions([
                'emag.cache.filesystem.adapter' => (new Definition(FilesystemAdapter::class))->addArgument('expr_cache'),
                'emag.cache.expression.language'=> (new Definition(ExpressionLanguage::class))->addArgument(new Reference('emag.cache.filesystem.adapter')),
            ]);
        } elseif ($config['expression_language']) {
            $container->setAlias('emag.cache.expression.language', $config['expression_language']);
        }

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

