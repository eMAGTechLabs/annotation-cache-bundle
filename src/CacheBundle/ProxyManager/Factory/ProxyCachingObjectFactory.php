<?php

namespace CacheBundle\ProxyManager\Factory;


use CacheBundle\ProxyManager\ProxyGenerator\CachedObjectGenerator;
use ProxyManager\Factory\AbstractBaseFactory;
use ProxyManager\ProxyGenerator\ProxyGeneratorInterface;

class ProxyCachingObjectFactory extends AbstractBaseFactory
{

    protected function getGenerator(): ProxyGeneratorInterface
    {
        return new CachedObjectGenerator();
    }


    public function createProxy(string $className) : string {
        $proxyClassName = $this->generateProxy($className);

        return $proxyClassName;
    }
}