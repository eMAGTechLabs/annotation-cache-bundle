<?php

namespace Emag\CacheBundle\Tests\Helpers;

use Emag\CacheBundle\Annotation\Cache;

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