<?php

namespace Emag\CacheBundle\ProxyManager;

use Emag\CacheBundle\Annotation\Cache;
use Emag\CacheBundle\Annotation\CacheExpression;
use Emag\CacheBundle\Exception\CacheException;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\Reader;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

trait CacheableClassTrait
{
    /**
     * Long name to avoid collision
     * @var CacheItemPoolInterface
     */
    protected $cacheServiceForMethod;

    /**
     * Long name to avoid colision
     * @var AnnotationReader
     *
     */
    protected $readerForCacheMethod;

    protected $__expressionLanguage;

    /**
     * @param CacheItemPoolInterface $cacheServiceForMethod
     */
    public function setCacheServiceForMethod(CacheItemPoolInterface $cacheServiceForMethod)
    {
        $this->cacheServiceForMethod = $cacheServiceForMethod;
    }

    /**
     * @param Reader $readerForCacheMethod
     */
    public function setReaderForCacheMethod(Reader $readerForCacheMethod)
    {
        $this->readerForCacheMethod = $readerForCacheMethod;
    }

    public function setExpressionLanguage(ExpressionLanguage $language = null)
    {
        $this->__expressionLanguage = $language;
    }

    public function getCached(\ReflectionMethod $method, $params)
    {
        $method->setAccessible(true);
        /** @var Cache $annotation */
        $annotation = $this->readerForCacheMethod->getMethodAnnotation($method, Cache::class);

        if ($annotation instanceof CacheExpression) {
            $annotation
                ->setContext($this)
                ->setExpressionLanguage($this->__expressionLanguage)
            ;
        }
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
     * @param \ReflectionMethod $method
     * @param $params
     * @param Cache $cacheObj
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

        $cacheKey = $cacheObj->getCache() .  sha1($cacheKey);

        return $cacheKey;
    }
}
