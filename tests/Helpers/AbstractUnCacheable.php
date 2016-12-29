<?php

namespace CacheBundle\Tests\Helpers;

use CacheBundle\Annotation\Cache;

abstract class AbstractUnCacheable
{
    /**
     * @Cache(cache="abstractMethod")
     *
     * @return int
     */
    abstract public function getCachedResult() : int;
}