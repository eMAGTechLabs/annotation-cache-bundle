<?php

namespace EmagTechLabs\CacheBundle\Tests\Helpers;

use EmagTechLabs\CacheBundle\Annotation\Cache;

class FinalUnCacheable
{

    /**
     * @Cache(cache="finalMethod")
     *
     * @return int
     */
    final public function getFinalMethod(): int
    {
        return rand();
    }
}