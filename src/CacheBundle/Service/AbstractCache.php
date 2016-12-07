<?php
namespace CacheBundle\Service;

use CacheBundle\Exception\CacheException;
use Predis\Client;

abstract class AbstractCache
{
    /**
     * Tries to add new key
     * @param $key
     * @param $value
     * @param int $ttl
     * @return bool
     * @throws CacheException
     */
    abstract public function add($key, $value, $ttl = 600);
    /**
     * @param $key
     * @param $value
     * @param int $ttl
     * @return bool
     * @throws CacheException
     */
    abstract public function set($key, $value, $ttl = 600);

    /**
     * Checks if a cache key exists
     * @param $key
     * @return bool
     */
    abstract public function has($key);

    /**
     * Retrieves info from a cache key
     * @param $key
     * @return mixed
     */
    abstract public function get($key);

    /**
     * Retrieves the ttl of the specified cache key
     * @param $key
     * @return int
     */
    abstract public function ttl($key);

    /**
     * Deletes the cache key. If key does not exists, it will throw CacheException
     * @param $key
     * @return mixed
     * @throws CacheException
     */
    abstract public function delete($key);

    /**
     * Increases the ttl of the cache
     * @param $key
     * @param int $ttl
     */
    abstract public function refreshTtl($key, $ttl = 3600);

    /**
     * @param $key
     * @param int $ttl
     * @return bool
     * @throws CacheException
     */
    final public function lock($key, $ttl = 3600)
    {
        return $this->add('lock:' . $key, 1, $ttl);
    }


    /**
     * @param $key
     * @return bool
     * @throws CacheException
     */
    final public function unlock($key)
    {
        return $this->delete('lock:' . $key);
    }

    /**
     * @param $key
     * @return bool
     */
    final public function hasLock($key)
    {
        return ($this->get('lock:' . $key) == 1);
    }

    /**
     * @param $key
     * @param int $ttl
     */
    final   public function heartBeatLock($key, $ttl = 3600)
    {
        $this->refreshTtl('lock:' . $key, $ttl);
    }
}