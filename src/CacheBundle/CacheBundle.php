<?php

namespace CacheBundle;

use CacheBundle\DependencyInjection\CacheCompilerPass;
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
        spl_autoload_unregister($this->container->get('emag.cache.proxy.config')->getProxyAutoloader());
    }

    public function boot()
    {
        $this->autoloader = spl_autoload_register($this->container->get('emag.cache.proxy.config')->getProxyAutoloader());
    }

    public function build(ContainerBuilder $container)
    {
        $compilerPass = new CacheCompilerPass();

        $container->addCompilerPass($compilerPass);
        parent::build($container);
    }
}
