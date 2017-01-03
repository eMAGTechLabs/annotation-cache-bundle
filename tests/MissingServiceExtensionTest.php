<?php

namespace Emag\CacheBundle\Tests;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;

class MissingServiceExtensionTest extends KernelTestCase
{
    protected static function getKernelClass()
    {
        return get_class(new class('test_missing_service', []) extends Kernel
        {
            public function registerBundles()
            {
                return [
                    new \Emag\CacheBundle\EmagCacheBundle()
                ];
            }

            public function registerContainerConfiguration(LoaderInterface $loader)
            {
                $loader->load(__DIR__ . '/config_missing_service.yml');
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
     * @expectedException \Emag\CacheBundle\Exception\CacheException
     * @expectedExceptionMessageRegExp ~You\'ve referenced a un-existing service of name "[a-zA-z0-9._-]+", please provide another\!~
     */
    public function testMissingService()
    {
        static::$class = null;
        self::bootKernel(['environment' => 'test_missing_service']);
    }
}