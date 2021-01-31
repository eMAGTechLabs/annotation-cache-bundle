<?php

declare(strict_types=1);

namespace EmagTechLabs\AnnotationCacheBundle\Tests\Functional\Others;

use EmagTechLabs\AnnotationCacheBundle\Tests\Functional\AnnotationCacheTestCase;
use EmagTechLabs\AnnotationCacheBundle\Tests\Functional\App\Service\Cacheable\CacheableClass;

class CacheWarmupProxiesTest extends AnnotationCacheTestCase
{
    public function testClassCreated()
    {
        self::bootKernel(['environment' => 'test_with_warmer']);
        self::$kernel->getContainer()->get('cache_warmer')
            ->warmup(self::$kernel->getContainer()->getParameter('kernel.cache_dir'));

        $cachePath = self::$kernel->getContainer()->getParameter('annotation_cache.service.path');
        $classNameInflector = self::$kernel->getContainer()->get(
            'annotation_cache.proxy.config'
        )->getClassNameInflector();

        $filename = $this->getCacheFileName($cachePath, CacheableClass::class, $classNameInflector);

        $this->assertFileExists(
            $filename,
            'Cached file not created!'
        );
    }
}
