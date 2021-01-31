<?php

declare(strict_types=1);

namespace EmagTechLabs\AnnotationCacheBundle\Tests\Functional\Utils;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class PublicServiceForTestsCompilerCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$this->isPHPUnit()) {
            return;
        }

        foreach ($container->getDefinitions() as $id => $definition) {
            if (strpos($id, "annotation_cache") === false) {
                continue;
            }
            $definition->setPublic(true);
        }

        foreach ($container->getAliases() as $id => $definition) {
            if (strpos($id, "annotation_cache") === false) {
                continue;
            }
            $definition->setPublic(true);
        }
    }

    private function isPHPUnit(): bool
    {
        return defined('PHPUNIT_COMPOSER_INSTALL') || defined('__PHPUNIT_PHAR__');
    }
}
