<?php

declare(strict_types=1);

namespace EmagTechLabs\AnnotationCacheBundle\Tests\Functional\Others;

use EmagTechLabs\AnnotationCacheBundle\CacheWarmer\ProxyWarmer;
use EmagTechLabs\AnnotationCacheBundle\Tests\Functional\AnnotationCacheTestCase;
use EmagTechLabs\AnnotationCacheBundle\Tests\Functional\App\Service\Cacheable\CacheableMethod;

class BundleInitializationTest extends AnnotationCacheTestCase
{
    public function testInitBundle()
    {
        // Boot the kernel.
        static::bootKernel();

        // Get the container
        $container = static::$kernel->getContainer();

        // Test if you services exists
        $this->assertTrue($container->has('annotation_cache.warmup'));
        $service = $container->get('annotation_cache.warmup');
        $this->assertInstanceOf(ProxyWarmer::class, $service);
    }

    public function testBundleWithDifferentConfiguration()
    {
        // Boot the kernel.
        static::bootKernel(['environment' => 'test_cachable_methods']);

        // Get the container
        $container = static::$kernel->getContainer();

        // Test if you services exists
        $this->assertTrue($container->has('annotation_cache_test.testservice'));
        $service = $container->get('annotation_cache_test.testservice');
        $this->assertInstanceOf(CacheableMethod::class, $service);
    }
}
