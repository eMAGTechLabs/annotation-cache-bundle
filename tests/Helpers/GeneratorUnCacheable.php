<?php

namespace CacheBundle\Tests\Helpers;

use CacheBundle\Annotation\Cache;

class GeneratorUnCacheable
{
    /**
     * @Cache(cache="generatorMethod")
     *
     * @return int
     */
    public function getGeneratorCacheResults()
    {
        yield rand();

        yield rand();
    }
}