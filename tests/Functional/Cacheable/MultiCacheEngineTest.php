<?php

declare(strict_types=1);

namespace EmagTechLabs\AnnotationCacheBundle\Tests\Functional\Cacheable;

use EmagTechLabs\AnnotationCacheBundle\Tests\Functional\AnnotationCacheTestCase;

class MultiCacheEngineTest extends AnnotationCacheTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        self::bootKernel(['environment' => 'test_multi_service']);
    }

    public function testWith2Adapters()
    {
        $service = self::$kernel->getContainer()->get('annotation_cache_test.service');

        $normal = $service->getCachedTime();
        $explicit = $service->getCachedTimeDefault();
        $alternative = $service->getCachedTimeAlternative();

        $this->assertEquals($normal, $explicit);
        $this->assertNotEquals($alternative, $explicit);
    }
}
