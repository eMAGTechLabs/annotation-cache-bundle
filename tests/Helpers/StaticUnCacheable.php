<?php

namespace EmagTechLabs\CacheBundle\Tests\Helpers;

use EmagTechLabs\CacheBundle\Annotation\Cache;

class StaticUnCacheable
{
    /**
     * @Cache(cache="staticMethod")
     *
     * @return int
     */
    public static function getStaticCacheResult(): int
    {
        return rand();
    }
}