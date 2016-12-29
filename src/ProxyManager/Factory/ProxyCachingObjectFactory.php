<?php

namespace Emag\CacheBundle\ProxyManager\Factory;

use ProxyManager\Factory\AbstractBaseFactory;
use ProxyManager\ProxyGenerator\ProxyGeneratorInterface;

class ProxyCachingObjectFactory extends AbstractBaseFactory
{
    /**
     * @var ProxyGeneratorInterface
     */
    protected $generator;

    /**
     * @param ProxyGeneratorInterface $generator
     */
    public function setGenerator(ProxyGeneratorInterface $generator)
    {
        $this->generator = $generator;
    }

    protected function getGenerator(): ProxyGeneratorInterface
    {
        return $this->generator;
    }

    public function createProxy(string $className) : string {
        $proxyClassName = $this->generateProxy($className);

        return $proxyClassName;
    }
}
