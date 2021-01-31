<?php

declare(strict_types=1);

namespace EmagTechLabs\AnnotationCacheBundle\Tests\Functional\UnCacheable;

use EmagTechLabs\AnnotationCacheBundle\Tests\Functional\AnnotationCacheTestCase;
use Symfony\Component\DependencyInjection\Exception\BadMethodCallException;

class FinalMethodCacheTest extends AnnotationCacheTestCase
{
    public function testFinalMethod()
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage("Final methods can not be cached!");

        self::bootKernel(['environment' => 'test_final_method']);
    }
}
