<?php

declare(strict_types=1);

namespace EmagTechLabs\AnnotationCacheBundle\ProxyManager\Factory;

use ProxyManager\Factory\AbstractBaseFactory;
use ProxyManager\ProxyGenerator\ProxyGeneratorInterface;

class ProxyCachingObjectFactory extends AbstractBaseFactory
{
    /**
     * @var ProxyGeneratorInterface
     */
    private $generator;

    public function createProxy(string $className): string
    {
        return $this->generateProxy($className);
    }

    /**
     * @param ProxyGeneratorInterface $generator
     *
     * @return void
     */
    public function setGenerator(ProxyGeneratorInterface $generator): void
    {
        $this->generator = $generator;
    }

    protected function getGenerator(): ProxyGeneratorInterface
    {
        return $this->generator;
    }
}
