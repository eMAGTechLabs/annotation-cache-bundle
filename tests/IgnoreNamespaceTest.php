<?php

namespace EmagTechLabs\CacheBundle\Tests;

use CacheBundle\Tests\IgnoredHelpers\IgnoreCacheAnnotation;
use Doctrine\Common\Annotations\AnnotationRegistry;
use EmagTechLabs\CacheBundle\EmagCacheBundle;
use EmagTechLabs\CacheBundle\ProxyManager\Factory\ProxyCachingObjectFactory;
use EmagTechLabs\CacheBundle\Tests\Helpers\SimpleCacheableClass;
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
        return get_class(
            new class('test_ignore_namespace', []) extends Kernel {
                public function registerBundles()
                {
                    $dummyBundle = new class extends Bundle {
                        public function build(ContainerBuilder $container)
                        {
                            $container->addCompilerPass(new AddCacheWarmerPass());
                            parent::build($container);
                        }
                    };

                    return [
                        $dummyBundle,
                        new EmagCacheBundle(),
                    ];
                }

                public function registerContainerConfiguration(LoaderInterface $loader)
                {
                    $loader->load(__DIR__.'/config/config_ignore_namespace.yml');
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

    public function testIgnoredNamespace()
    {
        static::$class = static::$kernel = null;

        self::bootKernel(['environment' => 'test_ignore_namespace']);
        self::$kernel->getContainer()->get('cache_warmer')
            ->warmup(self::$kernel->getContainer()->getParameter('kernel.cache_dir'));

        $cachePath = self::$kernel->getContainer()->getParameter('emag.cacheable.service.path');
        /** @var ClassNameInflector $classNameInflector */
        $classNameInflector = self::$kernel->getContainer()->get('emag.cache.proxy.config')->getClassNameInflector();

        $cacheable = sprintf(
            '%s%s.php',
            $cachePath,
            str_replace(
                '\\',
                '',
                $classNameInflector->getProxyClassName(
                    SimpleCacheableClass::class,
                    [
                        'className'           => SimpleCacheableClass::class,
                        'factory'             => ProxyCachingObjectFactory::class,
                        'proxyManagerVersion' => Version::getVersion(),
                    ]
                )
            )
        );

        $this->assertFileExists($cacheable, 'Cached file not created!');

        $uncacheable = sprintf(
            '%s%s.php',
            $cachePath,
            str_replace(
                "\\",
                '',
                $classNameInflector->getProxyClassName(
                    IgnoreCacheAnnotation::class,
                    [
                        'className'           => IgnoreCacheAnnotation::class,
                        'factory'             => ProxyCachingObjectFactory::class,
                        'proxyManagerVersion' => Version::getVersion(),
                    ]
                )
            )
        );

        $this->assertFileNotExists($uncacheable, 'Cached file created!');
    }
}
