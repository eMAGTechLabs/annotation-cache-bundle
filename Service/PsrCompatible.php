<?php

namespace eMAG\CacheBundle\Service;

use eMAG\CacheBundle\Exception\CacheException;
use Psr\Cache\CacheItemPoolInterface;

class PsrCompatible extends AbstractCache
{

    /**
     * @var CacheItemPoolInterface
     */
    protected $backend;

    /**
     * Tries to add new key
     * @param $key
     * @param $value
     * @param int $ttl
     * @return bool
     * @throws CacheException
     */
    public function add($key, $value, $ttl = 600)
    {
        $item = $this->backend->getItem($key);
        if ($item->isHit()) {
            throw new CacheException('Lock already present');
        }
        $item->set($value);
        $item->expiresAfter($ttl);
        $this->backend->save($item);
    }

    /**
     * @param $key
     * @param $value
     * @param int $ttl
     * @return bool
     * @throws CacheException
     */
    public function set($key, $value, $ttl = 600)
    {
        $item = $this->backend->getItem($key);
        $item->set($value);
        $item->expiresAfter($ttl);
        $this->backend->save($item);
    }

    /**
     * Checks if a cache key exists
     * @param $key
     * @return bool
     */
    public function has($key)
    {
        return $this->backend->hasItem($key);
    }

    /**
     * Retrieves info from a cache key
     * @param $key
     * @return mixed
     */
    public function get($key)
    {
        return $this->backend->getItem($key)->get();
    }

    /**
     * Retrieves the ttl of the specified cache key
     * @param $key
     * @return int
     */
    public function ttl($key)
    {
        throw new CacheException('Unable to determine TTL!');
    }

    /**
     * Deletes the cache key. If key does not exists, it will throw CacheException
     * @param $key
     * @return mixed
     * @throws CacheException
     */
    public function delete($key)
    {
        $this->backend->deleteItem($key);
    }

    /**
     * Increases the ttl of the cache
     * @param $key
     * @param int $ttl
     */
    public function refreshTtl($key, $ttl = 3600)
    {
        $item = $this->backend->getItem($key)->expiresAfter($ttl);
        $this->backend->save($item);
    }

    /**
     * @param CacheItemPoolInterface $backend
     */
    public function setBackend(CacheItemPoolInterface $backend)
    {
        $this->backend = $backend;
    }
}