<?php

declare(strict_types=1);

namespace EmagTechLabs\AnnotationCacheBundle\Annotation;

use Doctrine\Common\Annotations\AnnotationReader;
use ReflectionClass;
use ReflectionMethod;

use const PHP_VERSION_ID;

class CacheReader
{
    /**
     * @var AnnotationReader|null
     */
    private $reader;

    public function __construct(AnnotationReader $reader = null)
    {
        $this->reader = $reader;
    }

    /**
     * @param ReflectionMethod|ReflectionClass|object $reflection
     * @return Cache|void
     */
    public function getAnnotation(object $reflection)
    {
        if (PHP_VERSION_ID >= 80000) {
            foreach ($reflection->getAttributes(Cache::class) as $attribute) {
                return $attribute->newInstance();
            }
        }

        if (!$this->reader) {
            return;
        }

        $annotation = $reflection instanceof ReflectionClass
            ? $this->reader->getClassAnnotation($reflection, Cache::class)
            : $this->reader->getMethodAnnotation($reflection, Cache::class);

        if ($annotation instanceof Cache) {
            return $annotation;
        }
    }
}
