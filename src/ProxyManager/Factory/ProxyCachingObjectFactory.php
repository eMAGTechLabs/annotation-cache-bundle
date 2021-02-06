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

    protected function getGenerator(): ProxyGeneratorInterface
    {
        return $this->generator;
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
}
