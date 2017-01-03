<?php

namespace Emag\CacheBundle\Tests\Helpers;

use Emag\CacheBundle\Annotation\Cache;

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