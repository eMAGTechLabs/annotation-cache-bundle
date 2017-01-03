<?php

namespace Emag\CacheBundle\Tests\Helpers;

use Emag\CacheBundle\Annotation\Cache;

abstract class AbstractUnCacheable
{
    /**
     * @Cache(cache="abstractMethod")
     *
     * @return int
     */
    abstract public function getCachedResult() : int;
}