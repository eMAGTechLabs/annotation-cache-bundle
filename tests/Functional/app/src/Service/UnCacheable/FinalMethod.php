<?php

declare(strict_types=1);

namespace EmagTechLabs\AnnotationCacheBundle\Tests\Functional\App\Service\UnCacheable;

use EmagTechLabs\AnnotationCacheBundle\Annotation\Cache;

class FinalMethod
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
