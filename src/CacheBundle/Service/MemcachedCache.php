<?php


namespace CacheBundle\Service;


use CacheBundle\Exception\CacheException;
use Memcached;

class MemcachedCache extends AbstractCache
{

    /**
     * @var  \Memcached
     */
    protected $client;

    public function setMemcachedClient(\Memcached $client)
    {
        $this->client = $client;
    }

    /**
     * @inheritdoc
     */
    public function set($key, $value, $ttl = 600)
    {
        $res = $this->client->set($key, serialize($value), $ttl);
        if ($res === false) {
            throw new CacheException('Unable to set key. '.$this->client->getResultMessage());
        }

        return $res;
    }

    /**
     * @inheritdoc
     */
    public function has($key)
    {
        return ($this->get($key) !== false);
    }

    /**
     * @inheritdoc
     */
    public function get($key)
    {
        $data = $this->client->get($key);
        if ($data !== false) {
            return unserialize($data);
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function ttl($key)
    {
        throw new CacheException('Unable to determine remaining ttl in memcached');
    }

    /**
     * @inheritdoc
     */
    public function delete($key)
    {
        $res = $this->client->delete($key);
        if ($res === false) {
            throw new CacheException('Unable to  delete key. '.$this->client->getResultMessage());
        }
    }

    /**
     * @inheritdoc
     */
    public function refreshTtl($key, $ttl = 3600)
    {
        $res = $this->client->touch($key, $ttl);
        if ($res === false) {
            throw new CacheException('Unable to refresh TTL. '.$this->client->getResultMessage());
        }
    }

    public function add($key, $value, $ttl = 600)
    {
        $res = $this->client->add($key, serialize($value), $ttl);
        if ($res === false) {
            throw new CacheException('Unable to add key. already present? '.$this->client->getResultMessage());
        }
    }
}