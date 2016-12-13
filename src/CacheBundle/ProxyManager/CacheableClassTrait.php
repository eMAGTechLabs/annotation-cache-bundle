<?php

namespace CacheBundle\ProxyManager;

use CacheBundle\Annotation\Cache as CacheAnnotation;
use CacheBundle\Exception\CacheException;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\Reader;
use Psr\Cache\CacheItemPoolInterface;

trait CacheableClassTrait
{
    /**
     * Long name to avoid collision
     *
     * @var CacheItemPoolInterface
     */
    protected $cacheServiceForMethod;

    /**
     * Long name to avoid colision
     *
     * @var AnnotationReader
     */
    protected $readerForCacheMethod;

    /**
     * @param   CacheItemPoolInterface $cacheServiceForMethod
     *
     * @return  void
     */
    public function setCacheServiceForMethod(CacheItemPoolInterface $cacheServiceForMethod)
    {
        $this->cacheServiceForMethod = $cacheServiceForMethod;
    }

    /**
     * @param   Reader $readerForCacheMethod
     *
     * @return  void
     */
    public function setReaderForCacheMethod(Reader $readerForCacheMethod)
    {
        $this->readerForCacheMethod = $readerForCacheMethod;
    }

    /**
     * @param   \ReflectionMethod   $method
     * @param   array               $params
     *
     * @return  mixed
     */
    public function getCached(\ReflectionMethod $method, $params)
    {
        $method->setAccessible(true);
        /** @var CacheAnnotation $annotation */
        $annotation = $this->readerForCacheMethod->getMethodAnnotation($method, CacheAnnotation::class);

        $cacheKey = $this->getCacheKey($method, $params, $annotation);

        $cacheItem = $this->cacheServiceForMethod->getItem($cacheKey);

        if ($cacheItem->isHit() && !$annotation->isReset()) {
            return $cacheItem->get();
        }

        $result = $method->invokeArgs($this, $params);

        $cacheItem->set($result);
        $cacheItem->expiresAfter($annotation->getTtl());
        $this->cacheServiceForMethod->save($cacheItem);

        return $result;
    }

    /**
     * @param   \ReflectionMethod   $method
     * @param   array               $params
     * @param   CacheAnnotation     $cacheAnnotation
     *
     * @return  string
     *
     * @throws  CacheException
     */
    protected function getCacheKey(\ReflectionMethod $method, array $params, CacheAnnotation $cacheAnnotation)
    {
        $refParams = $method->getParameters();
        $defaultParams = [];
        foreach ($refParams as $id => $param) {
            try {
                $defaultValue = $param->getDefaultValue();
                $defaultParams[$id] = $defaultValue;
            } catch (\ReflectionException $e) {
                //do  nothing
            }

        }

        $arguments = $defaultParams;

        foreach ($refParams as $id => $param) {
            if (array_key_exists($id, $params)) {
                $arguments[$id] = $params[$id];
            }
        }

        $cacheKey = '';
        if (empty($cacheAnnotation->getKey())) {
            $cacheKey = sprintf('%s::%s_no_params', $method->getDeclaringClass()->getName(), $method->getName());
        }

        if (!empty($cacheAnnotation->getKey())) {
            $paramsToCache = array_map('trim', explode(',', $cacheAnnotation->getKey()));
            $paramsToCache = array_combine($paramsToCache, $paramsToCache);

            foreach ($refParams as $id => $param) {
                if (in_array($param->getName(), $paramsToCache)) {
                    if (is_scalar($arguments[$id])) {
                        $cacheKey .= '_' . $arguments[$id];
                    } else {
                        $cacheKey .= '_' . serialize($arguments[$id]);
                    }
                    unset($paramsToCache[$param->getName()]);
                }
            }

            if (!empty($paramsToCache)) {
                throw new CacheException('Not all requested params can be used in cache key. Missing ' . implode(',', $paramsToCache));
            }
        }

        $cacheKey = $cacheAnnotation->getCache() .  sha1($cacheKey);

        return $cacheKey;
    }
}
