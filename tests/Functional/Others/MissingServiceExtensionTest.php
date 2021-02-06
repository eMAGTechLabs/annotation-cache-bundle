<?php

declare(strict_types=1);

namespace EmagTechLabs\AnnotationCacheBundle\Tests\Functional\Others;

use EmagTechLabs\AnnotationCacheBundle\Exception\CacheException;
use EmagTechLabs\AnnotationCacheBundle\Tests\Functional\AnnotationCacheTestCase;

class MissingServiceExtensionTest extends AnnotationCacheTestCase
{
    public function testMissingService()
    {
        $this->expectException(CacheException::class);
        $this->expectExceptionMessageMatches(
            '~You\'ve referenced a un-existing service of name "[a-zA-z0-9._-]+", please provide another\!~'
        );
        self::bootKernel(['environment' => 'test_missing_service']);
    }
}
