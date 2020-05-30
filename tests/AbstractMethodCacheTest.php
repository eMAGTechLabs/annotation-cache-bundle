<?php

namespace EmagTechLabs\CacheBundle\Tests;

use Doctrine\Common\Annotations\AnnotationRegistry;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;

class AbstractMethodCacheTest extends KernelTestCase
{
    protected static function getKernelClass()
    {
        return get_class(
            new class('test_abstract_method', []) extends Kernel {
                public function registerBundles()
                {
                    return [
                        new \EmagTechLabs\CacheBundle\EmagCacheBundle(),
                    ];
                }

                public function registerContainerConfiguration(LoaderInterface $loader)
                {
                    $loader->load(__DIR__.'/config/config_abstract.yml');
                }

                public function __construct($environment, $debug)
                {
                    parent::__construct($environment, $debug);

                    $loader = require __DIR__.'/../vendor/autoload.php';

                    AnnotationRegistry::registerLoader([$loader, 'loadClass']);
                    $this->rootDir = __DIR__.'/app/';
                }
            }
        );
    }

    public function tearDown()
    {
        static::$class = null;
    }

    /**
     * @expectedException \Symfony\Component\DependencyInjection\Exception\BadMethodCallException
     * @expectedExceptionMessage Abstract methods can not be cached!
     */
    public function testAbstractMethods()
    {
        static::$class = null;

        self::bootKernel(['environment' => 'test_abstract_method']);
    }
}
