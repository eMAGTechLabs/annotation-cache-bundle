<?php

namespace Emag\CacheBundle\Tests;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;

class IncorrectExpressionLanguageServiceTest extends KernelTestCase
{
    protected static function getKernelClass()
    {
        return get_class(new class('test_incorrect_expr_lang_service', []) extends Kernel
        {
            public function registerBundles()
            {
                return [
                    new \Emag\CacheBundle\EmagCacheBundle()
                ];
            }

            public function registerContainerConfiguration(LoaderInterface $loader)
            {
                $loader->load(__DIR__ . '/config_incorrect_expr_lang_service.yml');
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
     * @expectedExceptionMessage You must provide a valid Expression Language service
     */
    public function testIncorrectService()
    {
        static::$class = null;
        self::bootKernel(['environment' => 'test_incorrect_expr_lang_service']);
    }
}