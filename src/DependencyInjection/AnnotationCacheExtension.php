<?php

declare(strict_types=1);

namespace EmagTechLabs\AnnotationCacheBundle\DependencyInjection;

use EmagTechLabs\AnnotationCacheBundle\Exception\CacheException;
use Exception;
use Psr\Cache\CacheItemPoolInterface;
use ReflectionClass;
use ReflectionException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class AnnotationCacheExtension extends Extension implements PrependExtensionInterface
{
    private const CONFIG_NAME = 'annotation_cache';
    private const CONFIG_PROVIDER_KEY = "provider";
    private const CONFIG_IGNORE_NAMESPACE_KEY = "ignore_namespaces";

    /**
     * @inheritDoc
     *
     * @throws ReflectionException
     * @throws CacheException
     *
     * @return void
     */
    public function prepend(ContainerBuilder $container)
    {
        $configuration = new Configuration($this->getAlias());
        $config = $this->processConfiguration($configuration, $container->getExtensionConfig($this->getAlias()));

        foreach ($config[self::CONFIG_PROVIDER_KEY] as $serviceId) {
            if (!$container->hasDefinition($serviceId)) {
                throw new CacheException(
                    sprintf(
                        'You\'ve referenced a un-existing service of name "%s", please provide another!',
                        $serviceId
                    )
                );
            }
            $provider = new ReflectionClass($container->getDefinition($serviceId)->getClass());
            if (!$provider->implementsInterface(CacheItemPoolInterface::class)) {
                throw new CacheException(
                    sprintf('You\'ve referenced a service "%s" that can not be used for caching!', $serviceId)
                );
            }
        }
    }

    public function getAlias(): string
    {
        return self::CONFIG_NAME;
    }

    /**
     * @inheritdoc
     *
     * @throws Exception
     *
     * @return void
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration($this->getAlias());
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('annotation_cache.ignore.namespaces', $config[self::CONFIG_IGNORE_NAMESPACE_KEY]);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yaml');

        $locatorDefinition = $container->getDefinition('annotation_cache.service.locator');
        $locatorArguments = [];
        foreach ($config[self::CONFIG_PROVIDER_KEY] as $alias => $serviceId) {
            $locatorArguments[$alias] = new Reference($serviceId);
        }
        $locatorDefinition->setArguments(
            [
                $locatorArguments,
            ]
        );
    }
}
