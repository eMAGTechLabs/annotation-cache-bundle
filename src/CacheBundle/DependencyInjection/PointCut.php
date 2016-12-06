<?php


namespace CacheBundle\DependencyInjection;

use CacheBundle\Annotation\Cache;
use CacheBundle\Tests\ExtendedCacheableClass;
use Doctrine\Common\Annotations\Reader;
use JMS\AopBundle\Aop\PointcutInterface;

class PointCut implements PointcutInterface
{
    /** @var Reader  */
    protected $reader;

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
        foreach ($class->getMethods() as $method) {
            $methodAnnotation = $this->reader->getMethodAnnotation($method, Cache::class);
            if ($methodAnnotation) {
                return true;
            }
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
        $methodAnnotation = $this->reader->getMethodAnnotation($method, Cache::class);
        if ($methodAnnotation) {
            return true;
        }

        return false;
    }
}