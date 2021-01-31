<?php

declare(strict_types=1);

namespace EmagTechLabs\AnnotationCacheBundle\CacheWarmer;

use EmagTechLabs\AnnotationCacheBundle\ProxyManager\Factory\ProxyCachingObjectFactory;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;

class ProxyWarmer implements CacheWarmerInterface
{
    /**
     * @var  ProxyCachingObjectFactory
     */
    private $factory;

    /**
     * @var array
     */
    private $classes = [];

    public function setFactory(ProxyCachingObjectFactory $factory): void
    {
        $this->factory = $factory;
    }

    public function addClassToGenerate(string $className): void
    {
        $this->classes[$className] = $className;
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
    public function warmUp(string $cacheDir)
    {
        foreach ($this->classes as $class) {
            $this->factory->createProxy($class);
        }
        return [];
    }
}
