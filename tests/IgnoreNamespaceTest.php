<?php

namespace CacheBundle\Tests;

use CacheBundle\ProxyManager\Factory\ProxyCachingObjectFactory;
use CacheBundle\Tests\Helpers\CacheableClass;
use CacheBundle\Tests\IgnoredHelpers\IgnoreCacheAnnotation;
use Doctrine\Common\Annotations\AnnotationRegistry;
use ProxyManager\Inflector\ClassNameInflector;
use ProxyManager\Version;
use Symfony\Bundle\FrameworkBundle\DependencyInjection\Compiler\AddCacheWarmerPass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\HttpKernel\Kernel;

class IgnoreNamespaceTest extends KernelTestCase
{
    protected static function getKernelClass()
    {
        return get_class(new class('test_with_warmer', []) extends Kernel
        {
            public function registerBundles()
            {
                $dummyBundle = new class extends Bundle
                {
                    public function build(ContainerBuilder $container)
                    {
                        $container->addCompilerPass(new AddCacheWarmerPass());
                        parent::build($container);
                    }
                };

                return [
                    $dummyBundle,
                    new \CacheBundle\CacheBundle(),
                ];
            }

            public function registerContainerConfiguration(LoaderInterface $loader)
            {
                $loader->load(__DIR__ . '/config_ignore_namespace.yml');
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

    public function testIgnoredNamespace()
    {
        static::$class = null;

        self::bootKernel(['environment' => 'test_with_warmer']);
        $cachePath = self::$kernel->getContainer()->getParameter('emag.cacheable.service.path');
        /** @var ClassNameInflector $classNameInflector */
        $classNameInflector = self::$kernel->getContainer()->get('emag.cache.proxy.config')->getClassNameInflector();

        self::$kernel->getContainer()->get('cache_warmer')
            ->warmup(self::$kernel->getContainer()->getParameter('kernel.cache_dir'));

        $cacheable = sprintf('%s%s.php', $cachePath, str_replace('\\', '', $classNameInflector->getProxyClassName(CacheableClass::class, [
            'className' => CacheableClass::class,
            'factory' => ProxyCachingObjectFactory::class,
            'proxyManagerVersion' => Version::getVersion()
        ])));

        $this->assertFileExists($cacheable, 'Cached file not created!');

        $uncacheable = sprintf('%s%s.php', $cachePath,  str_replace("\\", '', $classNameInflector->getProxyClassName(IgnoreCacheAnnotation::class, [
            'className' => IgnoreCacheAnnotation::class,
            'factory' => ProxyCachingObjectFactory::class,
            'proxyManagerVersion' => Version::getVersion()
        ])));

        $this->assertFileNotExists($uncacheable, 'Cached file created!');
    }

    public function tearDown()
    {
        static::$class = null;
    }
}
