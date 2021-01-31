<?php

declare(strict_types=1);

namespace EmagTechLabs\AnnotationCacheBundle;

use EmagTechLabs\AnnotationCacheBundle\DependencyInjection\AnnotationCacheExtension;
use EmagTechLabs\AnnotationCacheBundle\DependencyInjection\Compiler\CacheCompilerPass;
use ProxyManager\Configuration;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class AnnotationCacheBundle extends Bundle
{
    /**
     * @var callable|null
     */
    protected $autoloader = null;

    public function shutdown(): void
    {
        if (null !== $this->autoloader) {
            spl_autoload_unregister($this->autoloader);
            $this->autoloader = null;
        }
    }

    public function boot(): void
    {
        /** @var Configuration $proxyConfig */
        $proxyConfig = $this->container->get('annotation_cache.proxy.config');
        $this->autoloader = $proxyConfig->getProxyAutoloader();
        spl_autoload_register($this->autoloader);
    }

    /**
     * @return void
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new CacheCompilerPass());
    }

    public function getContainerExtension(): ?ExtensionInterface
    {
        return $this->extension ?? new AnnotationCacheExtension();
    }
}
