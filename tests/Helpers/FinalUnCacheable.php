<?php

namespace CacheBundle\Tests\Helpers;

use CacheBundle\Annotation\Cache;

class FinalUnCacheable
{

    /**
     * @Cache(cache="finalMethod")
     *
     * @return int
     */
    final public function getFinalMethod() : int
    {
        return rand();
    }
}