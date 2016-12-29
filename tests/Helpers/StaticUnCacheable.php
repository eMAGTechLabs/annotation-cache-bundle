<?php

namespace CacheBundle\Tests\Helpers;

use CacheBundle\Annotation\Cache;

class StaticUnCacheable
{
    /**
     * @Cache(cache="staticMethod")
     *
     * @return int
     */
    public static function getStaticCacheResult() : int
    {
        return rand();
    }
}