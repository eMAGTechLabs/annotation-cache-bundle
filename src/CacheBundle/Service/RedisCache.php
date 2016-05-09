<?php
namespace CacheBundle\Service;

use CacheBundle\Exception\CacheException;
use Predis\Client;

class RedisCache extends AbstractCache
{
    /**
     * @var Client
     */
    protected $redis;

    public function setRedis(Client $redis)
    {
        $this->redis = $redis;
    }

    /**
     * @deprecated
     */
    public function save($key, $value, $ttl = 600)
    {
        $this->set($key, $value, $ttl);
    }

    public function set($key, $value, $ttl = 600)
    {
        if (!is_scalar($key)) {
            throw  new CacheException('CACHE: Key value should be scalar');
        }

        if ($ttl > 0) {
            $setResult = $this->redis->setex(
                $key,
                intval($ttl),
                serialize($value)
            );
        } else {
            $setResult = $this->redis->set(
                $key,
                serialize($value)
            );
            $this->redis->persist($key);
        }

        return $setResult;
    }

    public function has($key)
    {
        return $this->redis->exists($key);
    }

    public function get($key)
    {
        $data = $this->redis->get(
            $key
        );
        if ($data != -2) {
            return unserialize($data);
        }

        return false;
    }

    public function ttl($key)
    {
        return $this->redis->ttl(
            $key
        );
    }

    public function delete($key)
    {
        $delResult = $this->redis->del($key);
        if ($delResult != 1) {
            throw new CacheException('CACHE: Key does not exist');
        }

        return $delResult;
    }

    /**
     * Increases the ttl of the cache
     *
     * @param     $key
     * @param int $ttl
     */
    public function refreshTtl($key, $ttl = 3600)
    {
        $this->redis->expire($key, $ttl);
    }

    /**
     * Tries to add new key
     *
     * @param     $key
     * @param     $value
     * @param int $ttl
     *
     * @return bool
     * @throws CacheException
     */
    public function add($key, $value, $ttl = 600)
    {
        $res = $this->redis->set($key, serialize($value), 'EX', $ttl, 'NX');
        if ($res != 'OK') {
            throw new CacheException('Lock already present');
        }
        return $res;
    }
}