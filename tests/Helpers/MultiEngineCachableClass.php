<?php

namespace EmagTechLabs\CacheBundle\Tests\Helpers;

use EmagTechLabs\CacheBundle\Annotation\Cache;

class MultiEngineCachableClass
{
    /**
     * @Cache(cache="xxx", ttl=30)
     *
     * @return int
     */
    public function getCachedTime(): int
    {
        return rand();
    }

    /**
     * @Cache(cache="xxx", ttl=30, storage="default")
     *
     * @return int
     */
    public function getCachedTimeDefault(): int
    {
        return rand();
    }

    /**
     * @Cache(cache="xxx", ttl=30, storage="alternative")
     *
     * @return int
     */
    public function getCachedTimeAlternative(): int
    {
        return rand();
    }
}
