<?php

declare(strict_types=1);

namespace EmagTechLabs\AnnotationCacheBundle\Tests\Functional\App\Service\Cacheable;

use EmagTechLabs\AnnotationCacheBundle\Annotation\Cache;

class ExtendableCacheableMethod
{
    /**
     * @Cache(cache="xxx", key="offset", ttl=30)
     * @param int $offset
     *
     * @return int
     */
    public function getCachedTime(int $offset = 0): int
    {
        return rand(1 + $offset, (int)microtime(true));
    }

    public function x(self $x)
    {
        //do stuff
    }
}
