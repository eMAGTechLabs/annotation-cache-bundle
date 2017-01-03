<?php

namespace CacheBundle\Tests\IgnoredHelpers;

use CacheBundle\Annotation\Cache;

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