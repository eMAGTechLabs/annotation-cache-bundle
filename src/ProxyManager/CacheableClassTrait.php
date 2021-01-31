<?php

declare(strict_types=1);

namespace EmagTechLabs\AnnotationCacheBundle\ProxyManager;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\Reader;
use EmagTechLabs\AnnotationCacheBundle\Annotation\Cache;
use EmagTechLabs\AnnotationCacheBundle\Exception\CacheException;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\InvalidArgumentException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionException;
use ReflectionMethod;

trait CacheableClassTrait
{
    /**
     * Long name to avoid collision
     * @var ContainerInterface
     */
    private $serviceLocatorCache;

    /**
     * Long name to avoid colision
     * @var AnnotationReader
     */
    private $readerForCacheMethod;

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
     * @param ReflectionMethod $method
     * @param $params
     * @return mixed
     * @throws CacheException
     * @throws InvalidArgumentException|ReflectionException
     */
    public function getCached(ReflectionMethod $method, $params)
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

        return $this->getResult($cacheItemPool, $cacheKey, $annotation, $method, $params);
    }

    /**
     * @param ReflectionMethod $method
     * @param $params
     * @param Cache $cacheObj
     * @return string
     * @throws CacheException
     */
    private function getCacheKey(ReflectionMethod $method, $params, Cache $cacheObj): string
    {
        $refParams = $method->getParameters();
        $arguments = $this->getArguments($refParams, $params);
        return $this->buildCacheKeyString($cacheObj, $refParams, $arguments);
    }

    private function getArguments(array $refParams, $params): array
    {
        $defaultParams = [];
        foreach ($refParams as $id => $param) {
            try {
                $defaultValue = $param->getDefaultValue();
                $defaultParams[$id] = $defaultValue;
            } catch (ReflectionException $e) {
                //do nothing
            }
        }

        $arguments = $defaultParams;
        foreach ($refParams as $id => $param) {
            if (array_key_exists($id, $params)) {
                $arguments[$id] = $params[$id];
            }
        }
        return $arguments;
    }

    /**
     * @param Cache $cacheObj
     * @param array $refParams
     * @param array $arguments
     * @return string
     * @throws CacheException
     */
    private function buildCacheKeyString(Cache $cacheObj, array $refParams, array $arguments): string
    {
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
                        $cacheKey .= '_' . $arguments[$id];
                    } else {
                        $cacheKey .= '_' . serialize($arguments[$id]);
                    }
                    unset($paramsToCache[$param->getName()]);
                }
            }

            if (!empty($paramsToCache)) {
                throw new CacheException(
                    'Not all requested params can be used in cache key. Missing ' . implode(',', $paramsToCache)
                );
            }
        }

        $cacheKey = $cacheObj->getCache() . sha1($cacheKey);
        return $cacheKey;
    }

    /**
     * @param string $label
     * @return CacheItemPoolInterface
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function getCacheService(string $label): CacheItemPoolInterface
    {
        return $this->serviceLocatorCache->get($label);
    }

    /**
     * @param CacheItemPoolInterface $cacheItemPool
     * @param string $cacheKey
     * @param Cache $annotation
     * @param ReflectionMethod $method
     * @param array $params
     * @return mixed
     * @throws InvalidArgumentException
     * @throws ReflectionException
     */
    private function getResult(
        CacheItemPoolInterface $cacheItemPool,
        string $cacheKey,
        Cache $annotation,
        ReflectionMethod $method,
        array $params
    ) {
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
}
