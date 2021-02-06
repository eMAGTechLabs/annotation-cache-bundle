<?php

declare(strict_types=1);

namespace EmagTechLabs\AnnotationCacheBundle\Tests\Functional\UnCacheable;

use EmagTechLabs\AnnotationCacheBundle\Tests\Functional\AnnotationCacheTestCase;
use Symfony\Component\DependencyInjection\Exception\BadMethodCallException;

class GeneratorMethodCacheTest extends AnnotationCacheTestCase
{
    public function testFinalMethod()
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage("Generator methods can not be cached!");
        static::$class = null;

        self::bootKernel(['environment' => 'test_generator_method']);
    }
}
