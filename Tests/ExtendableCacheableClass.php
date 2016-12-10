<?php

namespace CacheBundle\Tests;


use CacheBundle\Annotation\Cache;

class ExtendableCacheableClass
{
    /**
     * @Cache(cache="xxx", key="offset", ttl=30)
     * @param int $offset
     *
     * @return int
     */
    public function getCachedTime($offset = 0)
    {
        return rand(1 + $offset, microtime(true));
    }

    public function x(self $x)
    {
        //do stuff
    }

}