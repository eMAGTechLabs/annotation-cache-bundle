<?php

namespace eMAG\CacheBundle;

use eMAG\CacheBundle\DependencyInjection\Compiler\CacheCompilerPass;
use eMAG\CacheBundle\DependencyInjection\EMAGCacheExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class EMAGCacheBundle extends Bundle
{
    protected $autoloader;

    /**
     * @inheritDoc
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new CacheCompilerPass());
    }

    /**
     * @inheritDoc
     */
    public function boot()
    {
        $this->autoloader = spl_autoload_register($this->container->get('emag.cache.proxy.config')->getProxyAutoloader());
    }

    /**
     * @inheritDoc
     */
    public function shutdown()
    {
        if (null !== $this->autoloader) {
            spl_autoload_unregister($this->autoloader);
            $this->autoloader = null;
        }
    }

    /**
     * @inheritDoc
     */
    public function getContainerExtension()
    {
        if (null === $this->extension) {
            $this->extension = new EMAGCacheExtension();
        }

        return $this->extension;
    }
}

