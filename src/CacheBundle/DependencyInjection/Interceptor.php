<?php
namespace CacheBundle\DependencyInjection;

use CacheBundle\Annotation\Cache;
use CacheBundle\CacheCompilerPass;
use CacheBundle\ContextAwareCache;
use CacheBundle\Exception\CacheException;
use CG\Proxy\MethodInterceptorInterface;
use CG\Proxy\MethodInvocation;
use Doctrine\Common\Annotations\Reader;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class Interceptor implements MethodInterceptorInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    /** @var Reader  */
    protected $reader;
    /** @var CacheItemPoolInterface */
    protected $cacheService;

    /** @var  array */
    private $cacheData;

    public function __construct(CacheItemPoolInterface $cacheService, Reader $reader)
    {
        $this->cacheService = $cacheService;
        $this->reader = $reader;
    }

    /**
     * Called when intercepting a method call.
     *
     * @param MethodInvocation $invocation
     *
     * @return mixed the return value for the method invocation
     * @throws \Exception may throw any exception
     */
    public function intercept(MethodInvocation $invocation)
    {
        $refMethod = $invocation->reflection;
        /** @var Cache $annotation */
        $annotation = $this->reader->getMethodAnnotation($refMethod, CacheCompilerPass::CACHE_ANNOTATION_NAME);

        $cacheKey = $this->getCacheKey($invocation, $refMethod->getParameters(), $annotation);

        $cacheItem = $this->cacheService->getItem($cacheKey);

        if ($cacheItem->isHit() && !$annotation->isReset() && !($this->getCacheFlags($invocation->reflection) & Cache::STATE_RESET)) {
            $this->logger->debug('Cache hit for ' . $cacheKey);

            return $cacheItem->get();
        }

        $result = $invocation->proceed();

        $cacheItem->set($result);
        $cacheItem->expiresAfter($annotation->getTtl());
        $this->cacheService->save($cacheItem);

        return $result;
    }

    /**
     * @param MethodInvocation $invocation
     * @param \ReflectionParameter[] $refParams
     * @param Cache $cacheObj
     * @return string
     * @throws CacheException
     */
    protected function getCacheKey(MethodInvocation $invocation, $refParams, Cache $cacheObj)
    {
        $defaultParams = [];
        $refMethod = $invocation->reflection;
        $prefix = $this->cacheData[$invocation->reflection->getDeclaringClass()->getName()][$refMethod->getName()]['service_name'];
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
            $arguments[$id] = $invocation->arguments[$id];
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

        if ($invocation->object instanceof ContextAwareCache) {
            $cacheKey .= "_extra_" . $invocation->object->getExtraKey();
        }

        $cacheKey = $cacheObj->getCache() .  sha1($cacheKey);
        $this->logger->debug('Computed raw cache key: ' . $cacheKey);

        return $cacheKey;
    }

    /**
     * @param array $data
     */
    public function setCachedMethods($data)
    {
        $this->cacheData = $data;
    }

    public function getCacheFlags(\ReflectionMethod $method)
    {
        return $this->cacheData[$method->getDeclaringClass()->getName()][$method->getName()]['flags'];
    }
}