<?php

namespace EmagTechLabs\CacheBundle\Tests\Helpers;

use EmagTechLabs\CacheBundle\Annotation\Cache;

class SimpleCacheableClass
{
    /**
     * @Cache(cache="xxx", ttl=30)
     *
     * @return int
     */
    public function getCachedTime(): int
    {
        return rand();
    }
}
