<?php

declare(strict_types=1);

namespace EmagTechLabs\AnnotationCacheBundle\Tests\Functional\App\Service\UnCacheable;

use EmagTechLabs\AnnotationCacheBundle\Annotation\Cache;
use Generator;

class GeneratorMethod
{
    /**
     * @Cache(cache="generatorMethod")
     *
     * @return Generator
     */
    public function getGeneratorCacheResults()
    {
        yield rand();
    }
}
