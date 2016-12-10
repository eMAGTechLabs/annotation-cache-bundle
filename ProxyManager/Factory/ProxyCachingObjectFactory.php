<?php

namespace CacheBundle\ProxyManager\Factory;

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

    /**
     * @return  ProxyGeneratorInterface
     */
    protected function getGenerator()
    {
        return $this->generator;
    }

    /**
     * @param   string  $className
     *
     * @return  string
     */
    public function createProxy($className)
    {
        $proxyClassName = $this->generateProxy($className);

        return $proxyClassName;
    }
}