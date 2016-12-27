<?php

namespace CacheBundle\Tests;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;

class IncorrectCachingServiceTest extends KernelTestCase
{
    protected static function getKernelClass()
    {
        return get_class(new class('test_incorrect_service', []) extends Kernel
        {
            public function registerBundles()
            {
                return [
                    new \CacheBundle\CacheBundle()
                ];
            }

            public function registerContainerConfiguration(LoaderInterface $loader)
            {
                $loader->load(__DIR__ . '/config_incorrect_service.yml');
            }

            public function __construct($environment, $debug)
            {
                require __DIR__ . '/../vendor/autoload.php';

                parent::__construct($environment, $debug);

                $this->rootDir = __DIR__ . '/app/';
            }
        });
    }

    /**
     * @expectedException \CacheBundle\Exception\CacheException
     * @expectedExceptionMessageRegExp ~You\'ve referenced a service "[a-zA-z0-9._-]+" that can not be used for caching\!~
     */
    public function testIncorrectService()
    {
        static::$class = null;
        self::bootKernel(['environment' => 'test_incorrect_service']);
    }
}