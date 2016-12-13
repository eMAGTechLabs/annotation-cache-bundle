<?php

namespace CacheBundle\Service;

use CacheBundle\Exception\CacheException;

abstract class AbstractCache
{
    const LOCK_PREFIX = 'lock_';

    /**
     * Tries to add new key
     *
     * @param   string  $key
     * @param   mixed   $value
     * @param   int     $ttl
     *
     * @return  bool
     *
     * @throws  CacheException
     */
    abstract public function add($key, $value, $ttl = 600);

    /**
     * @param   string  $key
     * @param   mixed   $value
     * @param   int     $ttl
     *
     * @return  bool
     *
     * @throws  CacheException
     */
    abstract public function set($key, $value, $ttl = 600);

    /**
     * Checks if a cache key exists
     *
     * @param   string  $key
     *
     * @return  bool
     */
    abstract public function has($key);

    /**
     * Retrieves info from a cache key
     *
     * @param   string  $key
     *
     * @return  mixed
     */
    abstract public function get($key);

    /**
     * Retrieves the ttl of the specified cache key
     *
     * @param   string  $key
     *
     * @return  int
     */
    abstract public function ttl($key);

    /**
     * Deletes the cache key. If key does not exists, it will throw CacheException
     *
     * @param   string  $key
     *
     * @return  mixed
     *
     * @throws  CacheException
     */
    abstract public function delete($key);

    /**
     * Increases the ttl of the cache
     *
     * @param   string  $key
     * @param   int     $ttl
     *
     * @return  void
     */
    abstract public function refreshTtl($key, $ttl = 3600);

    /**
     * @param   string  $key
     * @param   int     $ttl
     *
     * @return  bool
     *
     * @throws  CacheException
     */
    final public function lock($key, $ttl = 3600)
    {
        return $this->add(self::LOCK_PREFIX . $key, 1, $ttl);
    }

    /**
     * @param   string  $key
     *
     * @return  bool
     *
     * @throws  CacheException
     */
    final public function unlock($key)
    {
        return $this->delete(self::LOCK_PREFIX . $key);
    }

    /**
     * @param   string  $key
     *
     * @return  bool
     */
    final public function hasLock($key)
    {
        return ($this->get(self::LOCK_PREFIX . $key) == 1);
    }

    /**
     * @param   string  $key
     * @param   int     $ttl
     *
     * @return  void
     */
    final   public function heartBeatLock($key, $ttl = 3600)
    {
        $this->refreshTtl(self::LOCK_PREFIX . $key, $ttl);
    }
}
