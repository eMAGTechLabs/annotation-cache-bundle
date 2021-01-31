<?php

declare(strict_types=1);

namespace EmagTechLabs\AnnotationCacheBundle\Tests\Functional\App\Service\UnCacheable;

use EmagTechLabs\AnnotationCacheBundle\Annotation\Cache;

abstract class AbstractMethod
{
    /**
     * @Cache(cache="abstractMethod")
     *
     * @return int
     */
    abstract public function getCachedResult(): int;
}
