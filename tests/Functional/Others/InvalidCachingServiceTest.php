<?php

declare(strict_types=1);

namespace EmagTechLabs\AnnotationCacheBundle\Tests\Functional\Others;

use EmagTechLabs\AnnotationCacheBundle\Exception\CacheException;
use EmagTechLabs\AnnotationCacheBundle\Tests\Functional\AnnotationCacheTestCase;

class InvalidCachingServiceTest extends AnnotationCacheTestCase
{
    public function testIncorrectService()
    {
        $this->expectException(CacheException::class);
        $this->expectExceptionMessageMatches(
            '~You\'ve referenced a service "[a-zA-z0-9._-]+" that can not be used for caching\!~'
        );
        self::bootKernel(['environment' => 'test_invalid_service']);
    }
}
