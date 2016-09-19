<?php

namespace CacheBundle\Service;


use CacheBundle\Exception\CacheException;

class ApcCache extends AbstractCache
{

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
        apc_add($key, $value, $ttl);
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
        apc_store($key, $value, $ttl);
    }

    /**
     * Checks if a cache key exists
     * @param $key
     * @return bool
     */
    public function has($key)
    {
        return apc_exists($key);
    }

    /**
     * Retrieves info from a cache key
     * @param $key
     * @return mixed
     */
    public function get($key)
    {
        return apc_fetch($key);
    }

    /**
     * Retrieves the ttl of the specified cache key
     * @param $key
     * @return int
     */
    public function ttl($key)
    {
       throw new CacheException('Unable to determine TTL for key in APC');
    }

    /**
     * Deletes the cache key. If key does not exists, it will throw CacheException
     * @param $key
     * @return mixed
     * @throws CacheException
     */
    public function delete($key)
    {
        apc_delete($key);
    }

    /**
     * Increases the ttl of the cache
     * @param $key
     * @param int $ttl
     */
    public function refreshTtl($key, $ttl = 3600)
    {
        throw new CacheException('Unable to extend TTL for key in APC');
    }
}