<?php

namespace CacheBundle\ProxyManager;


use CacheBundle\ProxyManager\Factory\ProxyCachingObjectFactory;

class CacheFactory
{
    /**
     * @var ProxyCachingObjectFactory
     */
    protected $generator;
    /**
     * @var \ProxyManager\Configuration
     */
    protected $proxyConfig;

    public function setProxyFactory(ProxyCachingObjectFactory $generator)
    {
        $this->generator = $generator;
    }

    public function setProxyConfig(\ProxyManager\Configuration $config)
    {
        $this->proxyConfig = $config;
    }

    public function generate($class, $arguments = [])
    {
        $proxyClassName = $this->proxyConfig->getClassNameInflector()->getProxyClassName($class, [
            'className' => $class,
            'factory' => ProxyCachingObjectFactory::class,
            'proxyManagerVersion' => \ProxyManager\Version::getVersion()
        ]);

        if (!class_exists($proxyClassName)) {
            $this->generator->createProxy($class);
        }
        $reflectionClass = new \ReflectionClass($proxyClassName);
        if ($reflectionClass->hasMethod('__construct')) {
            return ($reflectionClass)->newInstance($arguments);
        } else {
            return ($reflectionClass)->newInstance();
        }

    }

}