<?php

namespace EmagTechLabs\CacheBundle\ProxyManager\Factory;

use ProxyManager\Factory\AbstractBaseFactory;
use ProxyManager\ProxyGenerator\ProxyGeneratorInterface;

class ProxyCachingObjectFactory extends AbstractBaseFactory
{
    /**
     * @var ProxyGeneratorInterface
     */
    protected $generator;

    public function createProxy(string $className): string
    {
        $proxyClassName = $this->generateProxy($className);

        return $proxyClassName;
    }

    protected function getGenerator(): ProxyGeneratorInterface
    {
        return $this->generator;
    }

    /**
     * @param ProxyGeneratorInterface $generator
     */
    public function setGenerator(ProxyGeneratorInterface $generator)
    {
        $this->generator = $generator;
    }
}
