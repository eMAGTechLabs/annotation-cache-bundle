<?php

namespace CacheBundle\ProxyManager\Factory;

use ProxyManager\Factory\AbstractBaseFactory;
use ProxyManager\ProxyGenerator\ProxyGeneratorInterface;
use ProxyManager\Version;
use Symfony\Component\Debug\Exception\ContextErrorException;
use Zend\Code\Generator\ClassGenerator;

class ProxyCachingObjectFactory extends AbstractBaseFactory
{
    /**
     * Cached checked class names
     *
     * @var string[]
     */
    private $checkedClasses = [];

    /**
     * @var ProxyGeneratorInterface
     */
    protected $generator;

    /**
     * @param   ProxyGeneratorInterface $generator
     *
     * @return  void
     */
    public function setGenerator(ProxyGeneratorInterface $generator)
    {
        $this->generator = $generator;
    }

    /**
     * @return  ProxyGeneratorInterface
     */
    protected function getGenerator(): ProxyGeneratorInterface
    {
        return $this->generator;
    }

    /**
     * @param   string  $className
     *
     * @return  string
     */
    public function createProxy(string $className) : string
    {
        $proxyClassName = $this->generateProxy($className);

        return $proxyClassName;
    }

    /**
     * @inheritDoc
     */
    protected function generateProxy(string $className, array $proxyOptions = []) : string
    {
        if (isset($this->checkedClasses[$className])) {
            return $this->checkedClasses[$className];
        }

        $proxyParameters = [
            'className'           => $className,
            'factory'             => get_class($this),
            'proxyManagerVersion' => Version::getVersion(),
        ];
        $proxyClassName  = $this
            ->configuration
            ->getClassNameInflector()
            ->getProxyClassName($className, $proxyParameters);
        try {
            if (! class_exists($proxyClassName)) {
                $this->generateProxyClass(
                    $proxyClassName,
                    $className,
                    $proxyParameters,
                    $proxyOptions
                );
            }
        } catch (ContextErrorException $e) {
            //Method changed signature
            $this->generateProxyClass(
                $proxyClassName,
                $className,
                $proxyParameters,
                $proxyOptions
            );
        }

        $this
            ->configuration
            ->getSignatureChecker()
            ->checkSignature(new \ReflectionClass($proxyClassName), $proxyParameters);

        return $this->checkedClasses[$className] = $proxyClassName;
    }

    private function generateProxyClass(
        string $proxyClassName,
        string $className,
        array $proxyParameters,
        array $proxyOptions = []
    ) {
        $className = $this->configuration->getClassNameInflector()->getUserClassName($className);
        $phpClass  = new ClassGenerator($proxyClassName);

        $this->getGenerator()->generate(new \ReflectionClass($className), $phpClass, $proxyOptions);

        $phpClass = $this->configuration->getClassSignatureGenerator()->addSignature($phpClass, $proxyParameters);

        $this->configuration->getGeneratorStrategy()->generate($phpClass, $proxyOptions);

        $autoloader = $this->configuration->getProxyAutoloader();

        $autoloader($proxyClassName);
    }
}
