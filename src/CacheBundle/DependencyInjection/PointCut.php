<?php


namespace CacheBundle\DependencyInjection;

use CacheBundle\Annotation\Cache;
use Doctrine\Common\Annotations\Reader;
use JMS\AopBundle\Aop\PointcutInterface;

class PointCut implements PointcutInterface
{
    /** @var  array */
    private $cacheData;

    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
    }

    /**
     * Determines whether the advice applies to instances of the given class.
     *
     * There are some limits as to what you can do in this method. Namely, you may
     * only base your decision on resources that are part of the ContainerBuilder.
     * Specifically, you may not use any data in the class itself, such as
     * annotations.
     *
     * @param \ReflectionClass $class
     *
     * @return boolean
     */
    public function matchesClass(\ReflectionClass $class)
    {
        if (array_key_exists($class->getName(), $this->cacheData)) {
            return true;
        }

        return false;
    }

    /**
     * Determines whether the advice applies to the given method.
     *
     * This method is not limited in the way the matchesClass method is. It may
     * use information in the associated class to make its decision.
     *
     * @param \ReflectionMethod $method
     *
     * @return boolean
     */
    public function matchesMethod(\ReflectionMethod $method)
    {
        if (!array_key_exists($method->getDeclaringClass()->getName(), $this->cacheData)) {
            return false;
        }
        $classCache = $this->cacheData[$method->getDeclaringClass()->getName()];

        if (array_key_exists($method->getName(), $classCache) && $classCache[$method->getName()] & Cache::STATE_ENABLED) {
                return true;
        }

        return false;
    }

    /**
     * @param array $data
     */
    public function setCachedMethods($data)
    {
        $this->cacheData = $data;
    }
}