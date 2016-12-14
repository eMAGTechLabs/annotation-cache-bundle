<?php


namespace CacheBundle\CacheWarmer;


use CacheBundle\ProxyManager\Factory\ProxyCachingObjectFactory;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;

class ProxyWarmer implements CacheWarmerInterface
{
    /** @var  ProxyCachingObjectFactory */
    protected $factory;
    protected $classes = [];

    public function setFactory(ProxyCachingObjectFactory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * Checks whether this warmer is optional or not.
     *
     * Optional warmers can be ignored on certain conditions.
     *
     * A warmer should return true if the cache can be
     * generated incrementally and on-demand.
     *
     * @return bool true if the warmer is optional, false otherwise
     */
    public function isOptional()
    {
        return false;
    }

    /**
     * Warms up the cache.
     *
     * @param string $cacheDir The cache directory
     */
    public function warmUp($cacheDir)
    {
        foreach ($this->classes as $class) {
            $this->factory->createProxy($class);
        }
    }

    public function addClassToGenerate($className)
    {
        $this->classes[$className] = $className;

    }
}