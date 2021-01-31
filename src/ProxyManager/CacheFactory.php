<?php

declare(strict_types=1);

namespace EmagTechLabs\AnnotationCacheBundle\ProxyManager;

use EmagTechLabs\AnnotationCacheBundle\ProxyManager\Factory\ProxyCachingObjectFactory;
use ProxyManager\Configuration as ProxyConfiguration;
use ReflectionException;

class CacheFactory
{
    /**
     * @var ProxyCachingObjectFactory
     */
    private $generator;

    /**
     * @var ProxyConfiguration
     */
    private $proxyConfig;

    /**
     * @param ProxyCachingObjectFactory $generator
     *
     * @return  void
     */
    public function setProxyFactory(ProxyCachingObjectFactory $generator)
    {
        $this->generator = $generator;
    }

    /**
     * @param ProxyConfiguration $config
     *
     * @return  void
     */
    public function setProxyConfig(ProxyConfiguration $config)
    {
        $this->proxyConfig = $config;
    }

    /**
     * @param string $class
     * @param array $arguments
     * @return object
     *
     * @throws ReflectionException
     */
    public function generate(string $class, array $arguments = [])
    {
        $proxyClassName = $this->generator->createProxy($class);

        $reflectionClass = new \ReflectionClass($proxyClassName);
        if ($reflectionClass->hasMethod('__construct')) {
            return ($reflectionClass)->newInstanceArgs($arguments);
        }

        return ($reflectionClass)->newInstance();
    }
}
