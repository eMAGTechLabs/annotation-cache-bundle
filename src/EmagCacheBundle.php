<?php

namespace Emag\CacheBundle;

use Emag\CacheBundle\DependencyInjection\Compiler\CacheCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class EmagCacheBundle extends Bundle
{
    protected $autoloader;

    public function shutdown()
    {
        if (null !== $this->autoloader) {
            spl_autoload_unregister($this->autoloader);
            $this->autoloader = null;
        }
    }

    public function boot()
    {
        $this->autoloader = $this->container->get('emag.cache.proxy.config')->getProxyAutoloader();
        spl_autoload_register($this->autoloader);
    }

    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new CacheCompilerPass());
    }
}
