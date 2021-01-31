<?php

declare(strict_types=1);

namespace EmagTechLabs\AnnotationCacheBundle\Tests\Functional\App\Service\Cacheable;

use EmagTechLabs\AnnotationCacheBundle\Annotation\Cache;

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
