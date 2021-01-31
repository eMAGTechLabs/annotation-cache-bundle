<?php

declare(strict_types=1);

namespace EmagTechLabs\AnnotationCacheBundle\Tests\Functional\App\Service\UnCacheable;

use EmagTechLabs\AnnotationCacheBundle\Annotation\Cache;

class StaticMethod
{
    /**
     * @Cache(cache="staticMethod")
     *
     * @return int
     */
    public static function getStaticCacheResult(): int
    {
        return rand();
    }
}
