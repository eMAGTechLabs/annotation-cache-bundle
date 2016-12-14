<?php

namespace CacheBundle;

use CacheBundle\DependencyInjection\Compiler\CacheCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class CacheBundle extends Bundle
{
    protected $autoloader;
    /** @var  \ProxyManager\Configuration */
    protected $config;
    protected $getProxyConfig;

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
        $compilerPass = new CacheCompilerPass();

        $container->addCompilerPass($compilerPass);
        parent::build($container);
    }
}
