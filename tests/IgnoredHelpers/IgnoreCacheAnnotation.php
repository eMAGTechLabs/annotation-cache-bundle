<?php

namespace CacheBundle\Tests\IgnoredHelpers;

use Emag\CacheBundle\Annotation\Cache;

class IgnoreCacheAnnotation
{
    /**
     * @Cache(cache="ignored")
     *
     * @return int
     */
    public function getCachedResult() : int
    {
        return rand();
    }
}