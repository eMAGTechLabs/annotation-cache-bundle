<?php

namespace Emag\CacheBundle\ProxyManager;

use Emag\CacheBundle\ProxyManager\Factory\ProxyCachingObjectFactory;
use ProxyManager\Configuration as ProxyConfiguration;
use ProxyManager\Version as ProxyVersion;

class CacheFactory
{
    /**
     * @var ProxyCachingObjectFactory
     */
    protected $generator;

    /**
     * @var ProxyConfiguration
     */
    protected $proxyConfig;

    /**
     * @param   ProxyCachingObjectFactory   $generator
     *
     * @return  void
     */
    public function setProxyFactory(ProxyCachingObjectFactory $generator)
    {
        $this->generator = $generator;
    }

    /**
     * @param   ProxyConfiguration  $config
     *
     * @return  void
     */
    public function setProxyConfig(ProxyConfiguration $config)
    {
        $this->proxyConfig = $config;
    }

    /**
     * @param   string  $class
     * @param   array   $arguments
     *
     * @return  object
     */
    public function generate($class, $arguments = [])
    {
        $proxyClassName = $this->proxyConfig->getClassNameInflector()->getProxyClassName($class, [
            'className' => $class,
            'factory' => ProxyCachingObjectFactory::class,
            'proxyManagerVersion' => ProxyVersion::getVersion()
        ]);

        if (!class_exists($proxyClassName)) {
            $this->generator->createProxy($class);
        }

        $reflectionClass = new \ReflectionClass($proxyClassName);
        if ($reflectionClass->hasMethod('__construct')) {
            return ($reflectionClass)->newInstanceArgs($arguments);
        }

        return ($reflectionClass)->newInstance();
    }
}

