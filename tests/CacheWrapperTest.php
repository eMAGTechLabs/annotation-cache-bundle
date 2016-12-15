<?php
namespace CacheBundle\Tests;

use CacheBundle\Annotation\Cache;
use CacheBundle\Tests\Helpers\CacheableClass;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Monolog\Handler\TestHandler;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\HttpKernel\Kernel;

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

    protected static function getKernelClass()
    {
        return get_class(new class('test', []) extends Kernel
        {

            /**
             * Returns an array of bundles to register.
             *
             * @return BundleInterface[] An array of bundle instances
             */
            public function registerBundles()
            {
                return [
                    new \Symfony\Bundle\MonologBundle\MonologBundle(),
                    new \CacheBundle\CacheBundle()
                ];
            }

            public function registerContainerConfiguration(LoaderInterface $loader)
            {
                $loader->load(__DIR__ . '/config.yml');
            }

            public function __construct($environment, $debug)
            {
                parent::__construct($environment, $debug);

                $loader = require __DIR__ . '/../vendor/autoload.php';

                AnnotationRegistry::registerLoader(array($loader, 'loadClass'));
                $this->rootDir = __DIR__ . '/app/';
            }
        });
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

    public function testWithSamePrefix()
    {
        /** @var CacheableClass $object */
        $object = $this->container->get('cache.testservice');

        $objectReflectionClass = new \ReflectionClass($object);
        $annotationReader = new AnnotationReader();

        /** @var Cache $methodOne */
        $methodOne = $annotationReader->getMethodAnnotation(new \ReflectionMethod($objectReflectionClass->getParentClass()->getName(), 'getComputationOneWithoutParametersSamePrefix'), Cache::class);
        /** @var Cache $methodTwo */
        $methodTwo = $annotationReader->getMethodAnnotation(new \ReflectionMethod($objectReflectionClass->getParentClass()->getName(), 'getComputationTwoWithoutParametersSamePrefix'), Cache::class);

        $resultOne = $object->getComputationOneWithoutParametersSamePrefix();
        $resultTwo = $object->getComputationTwoWithoutParametersSamePrefix();

        $this->assertEquals($methodOne->getCache(), $methodTwo->getCache());
        $this->assertNotEquals($resultOne, $resultTwo);
        sleep(1);
        $this->assertNotEquals($object->getComputationOneWithoutParametersSamePrefix(), $object->getComputationTwoWithoutParametersSamePrefix());
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
     * @expectedException \CacheBundle\Exception\CacheException
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

    public function testAccessToProtectedMethod()
    {
        $object = $this->container->get('cache.testservice');
        $result = $object->publicMethodThatCallsProtected();
        $this->assertEquals($result, $object->publicMethodThatCallsProtected());

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
