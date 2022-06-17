<?php

declare(strict_types=1);

namespace EmagTechLabs\AnnotationCacheBundle\Tests\Functional\App;

use Doctrine\Common\Annotations\AnnotationRegistry;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

class TestKernel extends BaseKernel
{
    use MicroKernelTrait;

    /**
     * @var CompilerPassInterface[]
     */
    private $compilerPasses = [];

    public function getCacheDir(): string
    {
        return __DIR__ . '/../../../../build/tests/cache/';
    }

    public function getLogDir(): string
    {
        return __DIR__ . '/../../../../build/tests/logs/';
    }

    public function getProjectDir(): string
    {
        return __DIR__ . '/..';
    }

    public function __construct(string $environment, bool $debug)
    {
        parent::__construct($environment, $debug);
        $loader = require  __DIR__ . '/../../../../vendor/autoload.php';

        AnnotationRegistry::registerLoader(array($loader, 'loadClass'));
    }

    /**
     * @param CompilerPassInterface[] $compilerPasses
     */
    public function addCompilerPasses(array $compilerPasses)
    {
        $this->compilerPasses = $compilerPasses;
    }

    protected function configureContainer(ContainerConfigurator $container): void
    {
        $container->import('../config/{packages}/*.yaml');
        $container->import('../config/{packages}/' . $this->environment . '/*.yaml');

        if (is_file(\dirname(__DIR__) . '/config/services.yaml')) {
            $container->import(\dirname(__DIR__) . '/config/services.yaml');
            $container->import(\dirname(__DIR__) . '/config/{services}_' . $this->environment . '.yaml');
        } elseif (is_file($path = \dirname(__DIR__) . '/config/services.php')) {
            (require $path)($container->withPath($path), $this);
        }
    }

    protected function buildContainer(): ContainerBuilder
    {
        $container = parent::buildContainer();

        foreach ($this->compilerPasses as $pass) {
            $container->addCompilerPass($pass, PassConfig::TYPE_BEFORE_REMOVING, 10);
        }

        return $container;
    }
}
