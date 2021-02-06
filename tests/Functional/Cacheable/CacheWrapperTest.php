<?php

declare(strict_types=1);

namespace EmagTechLabs\AnnotationCacheBundle\Tests\Functional\Cacheable;

use EmagTechLabs\AnnotationCacheBundle\Exception\CacheException;
use EmagTechLabs\AnnotationCacheBundle\Tests\Functional\AnnotationCacheTestCase;
use EmagTechLabs\AnnotationCacheBundle\Tests\Functional\App\Service\Cacheable\CacheableMethod;

class CacheWrapperTest extends AnnotationCacheTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        self::bootKernel(['environment' => 'test_cachable_methods']);
    }

    public function testWithParams()
    {
        /** @var CacheableMethod $object */
        $object = self::$container->get("annotation_cache_test.testservice");

        $data = $object->getCachedTime();
        $dataWithParam = $object->getCachedTime(300);
        sleep(1);
        $this->assertEquals($data, $object->getCachedTime());
        $this->assertEquals($dataWithParam, $object->getCachedTime(300));
    }

    public function testWithParamsExtendedClass()
    {
        $object = static::$container->get('annotation_cache_test.testservice.extended');

        $data = $object->getCachedTime();
        $dataWithParam = $object->getCachedTime(300);
        sleep(1);
        $this->assertEquals($data, $object->getCachedTime());
        $this->assertEquals($dataWithParam, $object->getCachedTime(300));
    }

    public function testPHP8Annotation()
    {
        $object = static::$container->get('annotation_cache_test.testservice');

        $data = $object->getRandPHP8Annotation();
        if (\PHP_VERSION_ID >= 80000) {
            $this->assertEquals($data, $object->getRandPHP8Annotation());
        } else {
            $this->assertNotEquals($data, $object->getRandPHP8Annotation());
        }
        $this->assertNotEquals($data, $object->getRandPHP8AnnotationWithReset());
    }


    public function testReset()
    {
        $object = static::$container->get('annotation_cache_test.testservice');

        $data = $object->getCachedTime();

        $this->assertEquals($data, $object->getCachedTime());
        $this->assertNotEquals($data, $object->getCachedTimeWithReset());
    }

    public function testWithMultiParams()
    {
        $object = static::$container->get('annotation_cache_test.testservice');
        $result = $object->testWithMultipleParams(200, 300);

        $this->assertEquals($result, $object->testWithMultipleParams(200, 300));
        $this->assertEquals($result, $object->testWithMultipleParams(200, 150));
        $this->assertEquals($result, $object->testWithMultipleParams(200, 150, 100));
        $this->assertNotEquals($result, $object->testWithMultipleParams(200, 150, 200));
    }

    public function testWithWrongParamNames()
    {
        $this->expectException(CacheException::class);
        $this->expectExceptionMessage("Missing param3");

        $object = static::$container->get('annotation_cache_test.testservice');
        $object->testWithWrongParams(200, 300);
    }

    public function testMethodWithoutParams()
    {
        $object = static::$container->get('annotation_cache_test.testservice');
        $result = $object->testWithoutParams();
        $this->assertEquals($result, $object->testWithoutParams());
    }

    public function testAccessToProtectedMethod()
    {
        $object = static::$container->get('annotation_cache_test.testservice');
        $result = $object->publicMethodThatCallsProtected();
        $this->assertEquals($result, $object->publicMethodThatCallsProtected());
    }

    public function testServiceWithConstructor()
    {
        $object = static::$container->get('annotation_cache_test.testservice');
        $this->assertLessThanOrEqual(static::$container->getParameter('max.value'), $object->getRandomInteger());
    }

    public function testServiceWithArrayParameter()
    {
        $object = static::$container->get('annotation_cache_test.testservice');
        $min = rand();
        $max = $min + rand() + 1;
        $result = $object->getResultFromArrayParameter([$min, $max]);
        sleep(1);
        $this->assertEquals($result, $object->getResultFromArrayParameter([$min, $max]));
        $this->assertLessThanOrEqual($max, $result);
        $this->assertGreaterThanOrEqual($min, $result);
    }
}
