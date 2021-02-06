<?php

declare(strict_types=1);

namespace EmagTechLabs\AnnotationCacheBundle\Tests\Functional\App\Service\Cacheable;

use EmagTechLabs\AnnotationCacheBundle\Annotation\Cache;

class SimpleCacheableClass
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
}
