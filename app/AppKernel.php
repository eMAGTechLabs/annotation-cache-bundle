<?php

use Doctrine\Common\Annotations\AnnotationRegistry;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        // vendor bundles
        $bundles = array(
            new Symfony\Bundle\MonologBundle\MonologBundle(),
            new CacheBundle\CacheBundle(),
            new JMS\AopBundle\JMSAopBundle(),
        );


        return $bundles;
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__ . '/config.yml');
    }

    public function __construct($environment, $debug)
    {
        parent::__construct($environment, $debug);

        $loader = require __DIR__.'/../vendor/autoload.php';

        AnnotationRegistry::registerLoader(array($loader, 'loadClass'));
    }
}
