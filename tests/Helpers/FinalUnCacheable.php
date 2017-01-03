<?php

namespace Emag\CacheBundle\Tests\Helpers;

use Emag\CacheBundle\Annotation\Cache;

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