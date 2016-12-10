<?php

namespace CacheBundle\DependencyInjection;

use CacheBundle\ProxyManager\Factory\ProxyCachingObjectFactory;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;

class ProxyWarmer implements CacheWarmerInterface
{
    /**
     * @var  ProxyCachingObjectFactory
     */
    protected $factory;

    /**
     * @var array
     */
    protected $classes = [];

    public function setFactory(ProxyCachingObjectFactory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * @inheritDoc
     */
    public function isOptional()
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function warmUp($cacheDir)
    {
        foreach ($this->classes as $class) {
            $this->factory->createProxy($class);
        }
    }

    /**
     * @param   string  $className
     */
    public function addClassToGenerate($className)
    {
        $this->classes[$className] = $className;
    }
}
