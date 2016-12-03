<?php

namespace CacheBundle\DependencyInjection;


use CacheBundle\Annotation\Cache;
use CacheBundle\Exception\CacheException;
use CacheBundle\Service\AbstractCache;
use Doctrine\Common\Annotations\Reader;
use Go\Aop\Aspect;
use Go\Aop\Intercept\MethodInvocation;
use Go\Lang\Annotation\Around;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class CachingAspect implements Aspect
{

    /** @var  AbstractCache */
    protected $cacheService;
    /** @var Reader */
    protected $reader;
    /** @var  LoggerInterface */
    protected $logger;

    public function __construct()
    {
        $this->logger = new NullLogger();
    }

    /**
     * @Around("@execution(CacheBundle\Annotation\Cache)")
     */
    public function aroundCacheable(MethodInvocation $invocation)
    {
        $refMethod = $invocation->getMethod();

        /** @var Cache $cacheObj */
        $cacheObj = $invocation->getMethod()->getAnnotations(Cache::class)[0];

        $cacheKey = $this->getCacheKey($invocation, $refMethod->getParameters(), $cacheObj);


        if ($cacheObj->isReset()) {
            $data = false;
        } else {
            $data = $this->cacheService->get($cacheKey);
        }

        if ($data !== false) {
            $this->logger->debug('Cache hit for ' . $cacheKey);

            return $data;
        }

        $result = $invocation->proceed();

        $this->cacheService->set($cacheKey, $result, $cacheObj->getTtl());

        return $result;
    }


    /**
     * @param MethodInvocation $invocation
     * @param \ReflectionParameter[] $refParams
     * @param Cache $cacheObj
     * @return string
     * @throws CacheException
     * @internal param $refMethod
     */
    protected function getCacheKey(MethodInvocation $invocation, $refParams, Cache $cacheObj)
    {
        $defaultParams = [];
        $prefix = '';
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
            try {
                $arguments[$id] = $invocation->getArguments()[$id];
            } catch (\Exception $e) {
                //missing argument
            }
        }

        $cacheKey = $prefix;
        if (empty($cacheObj->getKey())) {
            $cacheKey = '_no_params';
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

        if ($invocation->getThis() instanceof ContextAwareCache) {
            $cacheKey .= "_extra_" . $invocation->getThis()->getExtraKey();
        }

        $cacheKey = $cacheObj->getCache() . sha1($cacheKey);
        $this->logger->debug('Computed raw cache key: ' . $cacheKey);

        return $cacheKey;
    }

    /**
     * @param AbstractCache $cache
     */
    public function setCache(AbstractCache $cache)
    {
        $this->cache = $cache;
    }

    /**
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param AbstractCache $cacheService
     */
    public function setCacheService($cacheService)
    {
        $this->cacheService = $cacheService;
    }
}