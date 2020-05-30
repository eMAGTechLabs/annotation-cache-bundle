<?php

namespace EmagTechLabs\CacheBundle\ProxyManager;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\Reader;
use EmagTechLabs\CacheBundle\Annotation\Cache;
use EmagTechLabs\CacheBundle\Exception\CacheException;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

trait CacheableClassTrait
{
    /**
     * Long name to avoid collision
     *
     * @var ContainerInterface
     */
    protected $serviceLocatorCache;

    /**
     * Long name to avoid colision
     *
     * @var AnnotationReader
     *
     */
    protected $readerForCacheMethod;

    /**
     * @param ContainerInterface $serviceLocatorCache
     */
    public function setServiceLocatorCache(ContainerInterface $serviceLocatorCache)
    {
        $this->serviceLocatorCache = $serviceLocatorCache;
    }

    /**
     * @param Reader $readerForCacheMethod
     */
    public function setReaderForCacheMethod(Reader $readerForCacheMethod)
    {
        $this->readerForCacheMethod = $readerForCacheMethod;
    }

    /**
     * @param \ReflectionMethod $method
     * @param $params
     *
     * @return mixed
     * @throws CacheException
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function getCached(\ReflectionMethod $method, $params)
    {
        $method->setAccessible(true);
        /** @var Cache $annotation */
        $annotation = $this->readerForCacheMethod->getMethodAnnotation($method, Cache::class);

        $cacheKey = $this->getCacheKey($method, $params, $annotation);

        try {
            $cacheItemPool = $this->getCacheService($annotation->getStorage());
        } catch (NotFoundExceptionInterface $e) {
            throw new CacheException('Requested cache service not found', $e->getCode(), $e);
        } catch (ContainerExceptionInterface $e) {
            throw new CacheException($e->getMessage(), $e->getCode(), $e);
        }

        $cacheItem = $cacheItemPool->getItem($cacheKey);

        if ($cacheItem->isHit() && !$annotation->isReset()) {
            return $cacheItem->get();
        }

        $result = $method->invokeArgs($this, $params);

        $cacheItem->set($result);
        $cacheItem->expiresAfter($annotation->getTtl());
        $cacheItemPool->save($cacheItem);

        return $result;
    }

    /**
     * @param \ReflectionMethod $method
     * @param $params
     * @param Cache $cacheObj
     *
     * @return string
     * @throws CacheException
     */
    protected function getCacheKey(\ReflectionMethod $method, $params, Cache $cacheObj)
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
        if (empty($cacheObj->getKey())) {
            $cacheKey = 'no_params_';
        }

        if (!empty($cacheObj->getKey())) {
            $paramsToCache = array_map('trim', explode(',', $cacheObj->getKey()));
            $paramsToCache = array_combine($paramsToCache, $paramsToCache);

            foreach ($refParams as $id => $param) {
                if (in_array($param->getName(), $paramsToCache)) {
                    if (is_scalar($arguments[$id])) {
                        $cacheKey .= '_'.$arguments[$id];
                    } else {
                        $cacheKey .= '_'.serialize($arguments[$id]);
                    }
                    unset($paramsToCache[$param->getName()]);
                }
            }

            if (!empty($paramsToCache)) {
                throw new CacheException(
                    'Not all requested params can be used in cache key. Missing '.implode(',', $paramsToCache)
                );
            }
        }

        $cacheKey = $cacheObj->getCache().sha1($cacheKey);

        return $cacheKey;
    }

    /**
     * @param string $label
     *
     * @return CacheItemPoolInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    protected function getCacheService(string $label): CacheItemPoolInterface
    {
        return $this->serviceLocatorCache->get($label);
    }
}
