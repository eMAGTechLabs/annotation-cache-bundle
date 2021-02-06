<?php

declare(strict_types=1);

namespace EmagTechLabs\AnnotationCacheBundle\Tests\Functional\App\Service\Ignored;

use EmagTechLabs\AnnotationCacheBundle\Annotation\Cache;

class IgnoreCacheAnnotation
{
    /**
     * @Cache(cache="ignored")
     *
     * @return int
     */
    public function getCachedResult(): int
    {
        return rand();
    }
}
