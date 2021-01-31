<?php

declare(strict_types=1);

namespace EmagTechLabs\AnnotationCacheBundle\Tests\Functional;

use EmagTechLabs\AnnotationCacheBundle\ProxyManager\Factory\ProxyCachingObjectFactory;
use EmagTechLabs\AnnotationCacheBundle\Tests\Functional\App\Kernel as FeatureKernel;
use EmagTechLabs\AnnotationCacheBundle\Tests\Functional\Utils\PublicServiceForTestsCompilerCompilerPass;
use ProxyManager\Inflector\ClassNameInflector;
use ProxyManager\Version;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

abstract class AnnotationCacheTestCase extends KernelTestCase
{
    /**
     * @var CompilerPassInterface[]
     */
    protected static $compilerPasses = [];

    protected static function getKernelClass()
    {
        return FeatureKernel::class;
    }

    protected static function createKernel(array $options = [])
    {
        $kernel = parent::createKernel($options);
        $kernel->addCompilerPasses(static::$compilerPasses);

        return $kernel;
    }

    protected function setUp(): void
    {
        parent::setUp();

        static::$class = null;
        static::addCompilerPass(new PublicServiceForTestsCompilerCompilerPass());
    }

    /**
     * @param CompilerPassInterface $compilerPass
     */
    protected static function addCompilerPass(CompilerPassInterface $compilerPass)
    {
        static::$compilerPasses[] = $compilerPass;
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        static::$class = null;
    }

    protected function getCacheFileName(string $cachePath, string $className, ClassNameInflector $classNameInflector)
    {
        return sprintf(
            '%s%s.php',
            $cachePath,
            str_replace(
                "\\",
                '',
                $classNameInflector->getProxyClassName(
                    $className,
                    [
                        'className' => $className,
                        'factory' => ProxyCachingObjectFactory::class,
                        'proxyManagerVersion' => Version::getVersion(),
                        'proxyOptions' => [],
                    ]
                )
            )
        );
    }
}
