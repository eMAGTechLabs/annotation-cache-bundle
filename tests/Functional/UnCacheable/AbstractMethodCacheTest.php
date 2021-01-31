<?php

declare(strict_types=1);

namespace EmagTechLabs\AnnotationCacheBundle\Tests\Functional\UnCacheable;

use EmagTechLabs\AnnotationCacheBundle\Tests\Functional\AnnotationCacheTestCase;
use Symfony\Component\DependencyInjection\Exception\BadMethodCallException;

class AbstractMethodCacheTest extends AnnotationCacheTestCase
{
    public function testAbstractMethods()
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage("Abstract methods can not be cached!");
        static::$class = null;

        self::bootKernel(['environment' => 'test_abstract_method']);
    }
}
