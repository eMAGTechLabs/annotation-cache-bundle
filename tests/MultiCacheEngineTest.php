<?php

namespace EmagTechLabs\CacheBundle\Tests;


use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;

class MultiCacheEngineTest extends KernelTestCase
{
    protected static function getKernelClass()
    {
        return get_class(
            new class('test_missing_service', []) extends Kernel {
                public function registerBundles()
                {
                    return [
                        new \EmagTechLabs\CacheBundle\EmagCacheBundle(),
                    ];
                }

                public function registerContainerConfiguration(LoaderInterface $loader)
                {
                    $loader->load(__DIR__.'/config/config_multi_service.yml');
                }

                public function __construct($environment, $debug)
                {
                    require __DIR__.'/../vendor/autoload.php';

                    parent::__construct($environment, $debug);

                    $this->rootDir = __DIR__.'/app/';
                }
            }
        );
    }

    public function testWith2Adapters()
    {
        static::$class = null;
        self::bootKernel(['environment' => 'multi_service']);

        $service = self::$kernel->getContainer()->get('service');


        $normal = $service->getCachedTime();
        $explicit = $service->getCachedTimeDefault();
        $alternative = $service->getCachedTimeAlternative();

        $this->assertEquals($normal, $explicit);

        $this->assertNotEquals($alternative, $explicit);
    }
}
