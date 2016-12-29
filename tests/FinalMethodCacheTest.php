<?php

namespace CacheBundle\Tests;

use Doctrine\Common\Annotations\AnnotationRegistry;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;

class FinalMethodCacheTest extends KernelTestCase
{
    protected static function getKernelClass()
    {
        return get_class(new class('test_final_method', []) extends Kernel
        {
            public function registerBundles()
            {
                return [
                    new \CacheBundle\CacheBundle()
                ];
            }

            public function registerContainerConfiguration(LoaderInterface $loader)
            {
                $loader->load(__DIR__ . '/config_final.yml');
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

    /**
     * @expectedException \Symfony\Component\DependencyInjection\Exception\BadMethodCallException
     * @expectedExceptionMessage Final methods can not be cached!
     */
    public function testFinalMethod()
    {
        static::$class = null;

        self::bootKernel(['environment' => 'test_final_method']);
    }

    public function tearDown()
    {
        static::$class = null;
    }
}
