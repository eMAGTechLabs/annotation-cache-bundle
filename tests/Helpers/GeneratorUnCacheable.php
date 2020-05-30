<?php

namespace EmagTechLabs\CacheBundle\Tests\Helpers;

use EmagTechLabs\CacheBundle\Annotation\Cache;

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