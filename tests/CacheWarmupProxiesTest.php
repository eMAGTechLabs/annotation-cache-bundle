<?php

namespace Emag\CacheBundle\Tests;

use Emag\CacheBundle\ProxyManager\Factory\ProxyCachingObjectFactory;
use Emag\CacheBundle\Tests\Helpers\CacheableClass;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Symfony\Bundle\FrameworkBundle\DependencyInjection\Compiler\AddCacheWarmerPass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\HttpKernel\Kernel;

class CacheWarmupProxiesTest extends KernelTestCase
{
    protected static function getKernelClass()
    {
        return get_class(new class('test-ww', []) extends Kernel
        {

            /**
             * Returns an array of bundles to register.
             *
             * @return BundleInterface[] An array of bundle instances
             */
            public function registerBundles()
            {
                $dummyBundle = new class extends Bundle
                {

                    public function build(ContainerBuilder $container)
                    {
                        $compilerPass = new AddCacheWarmerPass();

                        $container->addCompilerPass($compilerPass);
                        parent::build($container);
                    }
                };
                return [
                    new \Symfony\Bundle\MonologBundle\MonologBundle(),
                    new \Emag\CacheBundle\EmagCacheBundle(),
                    $dummyBundle,
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

    public function testClassCreated()
    {
        self::$class = null;
        self::bootKernel(['environment' => 'test_with_warmer']);
        self::$kernel->getContainer()->get('cache_warmer')
            ->warmup(self::$kernel->getContainer()->getParameter('kernel.cache_dir'));

        $filename = self::$kernel->getContainer()->get('emag.cache.proxy.config')->getClassNameInflector()
            ->getProxyClassName(CacheableClass::class, [
                'className' => CacheableClass::class,
                'factory' => ProxyCachingObjectFactory::class,
                'proxyManagerVersion' => \ProxyManager\Version::getVersion()
            ]);

        $filename = str_replace("\\", '', $filename) . '.php';

        $this->assertFileExists(
            self::$kernel->getContainer()->getParameter('emag.cacheable.service.path') . $filename,
            'Cached file not created!'
        );
    }
}
