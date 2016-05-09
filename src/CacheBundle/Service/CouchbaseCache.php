<?php
namespace CacheBundle\Service;

use CacheBundle\Exception\CacheException;

class CouchbaseCache extends AbstractCache
{

    /**
     * @var \CouchbaseBucket
     */
    protected $service;

    public function setCouchBase(\CouchbaseBucket $service)
    {
        $this->service = $service;
    }

    public function add($key, $value, $ttl = 600)
    {
        try {
            $this->service->insert($key, serialize($value), ['expiry' => $ttl]);
        } catch (\CouchbaseException $e) {
            throw new CacheException($e->getMessage(), 100, $e);
        }
    }

    /**
     * @param     $key
     * @param     $value
     * @param int $ttl
     *
     * @return bool
     * @throws CacheException
     */
    public function set($key, $value, $ttl = 600)
    {
        try {
            $this->service->upsert($key, serialize($value), ['expiry' => $ttl]);
        } catch (\CouchbaseException $e) {
            throw new CacheException($e->getMessage(), 100, $e);
        }
    }

    /**
     * Checks if a cache key exists
     *
     * @param string $key
     *
     * @return bool
     * @throws CacheException
     */
    public function has($key)
    {
        try {
            $this->service->get($key);
        } catch (\CouchbaseException $e) {
            return false;
        }

        return true;
    }

    /**
     * Retrieves info from a cache key
     *
     * @param string $key
     *
     * @return mixed
     * @throws CacheException
     */
    public function get($key)
    {
        try {
            return unserialize($this->service->get($key)->value);
        } catch (\CouchbaseException $e) {
            return false;
        }
    }

    /**
     * Retrieves the ttl of the specified cache key
     *
     * @param string $key
     *
     * @return int
     * @throws CacheException
     */
    public function ttl($key)
    {
        throw new CacheException('Unable to determine ttl in couchbase');
    }

    /**
     * Deletes the cache key. I f key does not exists, it will throw CacheException
     *
     * @param string $key
     *
     * @return mixed
     * @throws CacheException
     */
    public function delete($key)
    {
        try {
            $this->service->remove($key);
        } catch (\CouchbaseException $e) {
            throw new CacheException($e->getMessage(), 100, $e);
        }
    }

    /**
     * Increases the ttl of the cache
     *
     * @param string $key
     * @param int $ttl
     *
     * @throws CacheException
     */
    public function refreshTtl($key, $ttl = 3600)
    {
        $this->service->getAndTouch($key, $ttl);
    }
}