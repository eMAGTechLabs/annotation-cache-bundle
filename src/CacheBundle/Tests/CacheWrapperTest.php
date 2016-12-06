<?php
namespace CacheBundle\Tests;

use Monolog\Handler\TestHandler;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

class CacheWrapperTest extends KernelTestCase
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    public function setUp()
    {
        parent::setUp();

        self::bootKernel();
        $this->container = self::$kernel->getContainer();
    }

    public function testWithParams()
    {
        $object = $this->container->get('cache.testservice');

        $data = $object->getCachedTime();
        $dataWithParam = $object->getCachedTime(300);
        sleep(1);
        $this->assertEquals($data, $object->getCachedTime());
        $this->assertEquals($dataWithParam, $object->getCachedTime(300));
    }

    public function testWithParamsExtendedClass()
    {
        $object = $this->container->get('cache.testservice.extended');

        $data = $object->getCachedTime();
        $dataWithParam = $object->getCachedTime(300);
        sleep(1);
        $this->assertEquals($data, $object->getCachedTime());
        $this->assertEquals($dataWithParam, $object->getCachedTime(300));

    }

    public function testReset()
    {
        $object = $this->container->get('cache.testservice');


        $data = $object->getCachedTime();
        $this->assertEquals($data, $object->getCachedTime());

        $this->assertNotEquals($data, $object->getCachedTimeWithReset());
    }

    public function testWithMultiParams()
    {
        $object = $this->container->get('cache.testservice');
        $result = $object->testWithMultipleParams(200, 300);

        $this->assertEquals($result, $object->testWithMultipleParams(200, 300));
        $this->assertEquals($result, $object->testWithMultipleParams(200, 150));
        $this->assertEquals($result, $object->testWithMultipleParams(200, 150, 100));
        $this->assertNotEquals($result, $object->testWithMultipleParams(200, 150, 200));
    }

    /**
     * @expectedExceptionMessage Missing param3
     * @expectedException CacheBundle\Exception\CacheException
     */
    public function testWithWrongParamNames()
    {

        $object = $this->container->get('cache.testservice');
        $result = $object->testWithWrongParams(200, 300);
    }

    public function testMethodWithoutParams()
    {
        $object = $this->container->get('cache.testservice');
        $result = $object->testWithoutParams();
        $this->assertEquals($result, $object->testWithoutParams());
    }

    /**
     * Get TestHandler object
     *
     * @return TestHandler
     */
    protected function getLogHandler()
    {
        $testHandler = false;
        foreach ($this->container->get('logger')->getHandlers() as $handler) {
            if ($handler instanceof TestHandler) {
                $testHandler = $handler;
                break;
            }
        }

        if (!$testHandler instanceof TestHandler) {
            throw new \RuntimeException('TestHandler does not exists in monolog.');
        }

        return $testHandler;
    }
}
