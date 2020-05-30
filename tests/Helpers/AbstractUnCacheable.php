<?php

namespace EmagTechLabs\CacheBundle\Tests\Helpers;

use EmagTechLabs\CacheBundle\Annotation\Cache;

abstract class AbstractUnCacheable
{
    /**
     * @Cache(cache="abstractMethod")
     *
     * @return int
     */
    abstract public function getCachedResult(): int;
}