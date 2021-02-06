<?php

declare(strict_types=1);

namespace EmagTechLabs\AnnotationCacheBundle\Tests\Functional\Ignored;

use EmagTechLabs\AnnotationCacheBundle\Tests\Functional\AnnotationCacheTestCase;
use EmagTechLabs\AnnotationCacheBundle\Tests\Functional\App\Service\Cacheable\SimpleCacheableClass;
use EmagTechLabs\AnnotationCacheBundle\Tests\Functional\App\Service\Ignored\IgnoreCacheAnnotation;
use ProxyManager\Inflector\ClassNameInflector;

class IgnoreNamespaceTest extends AnnotationCacheTestCase
{
    public function testIgnoredNamespace()
    {
        self::bootKernel(['environment' => 'test_ignore_namespace']);
        self::$kernel->getContainer()->get('cache_warmer')
            ->warmup(self::$kernel->getContainer()->getParameter('kernel.cache_dir'));

        $cachePath = self::$kernel->getContainer()->getParameter('annotation_cache.service.path');
        /** @var ClassNameInflector $classNameInflector */
        $classNameInflector = self::$kernel->getContainer()->get(
            'annotation_cache.proxy.config'
        )->getClassNameInflector();

        $cacheable = $this->getCacheFileName($cachePath, SimpleCacheableClass::class, $classNameInflector);
        $this->assertFileExists($cacheable, 'Cached file not created!');

        $uncacheable = $this->getCacheFileName($cachePath, IgnoreCacheAnnotation::class, $classNameInflector);
        $this->assertFileNotCreated($uncacheable, 'Cached file created!');
    }
}
