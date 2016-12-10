<?php

namespace CacheBundle;

use CacheBundle\DependencyInjection\CacheCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class CacheBundle extends Bundle
{
    protected $autoloader;

    public function boot()
    {
        $this->autoloader = spl_autoload_register($this->container->get('emag.cache.proxy.config')->getProxyAutoloader());
    }

    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new CacheCompilerPass());
    }

    public function shutdown()
    {
        if (null !== $this->autoloader) {
            spl_autoload_unregister($this->autoloader);
            $this->autoloader = null;
        }
    }
}

