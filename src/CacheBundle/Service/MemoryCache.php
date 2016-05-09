<?php


namespace CacheBundle\Service;


use CacheBundle\Exception\CacheException;

class MemoryCache extends AbstractCache
{

    /**
     * @var array
     */
    static protected $data = [];

    /**
     * @inheritdoc
     */
    public function set($key, $value, $ttl = 600)
    {
        self::$data[$key] = [
            'data' => $value,
            'exp' => $ttl+time()
        ];
    }

    /**
     * @inheritdoc
     */
    public function has($key)
    {
        if (array_key_exists($key, self::$data)) {
            if (self::$data[$key]['exp'] > time()) {
                return true;
            }
        }
        return false;
    }

    /**
     * @inheritdoc
     */
    public function get($key)
    {
        if ($this->has($key)) {
            return self::$data[$key]['data'];
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function ttl($key)
    {
        if ($this->has($key)) {
            return self::$data[$key]['exp'] - time();
        }
        return -1;
    }

    /**
     * @inheritdoc
     */
    public function delete($key)
    {
        if ($this->has($key)) {
            unset(self::$data[$key]);
        }
    }

    /**
     * @inheritdoc
     */
    public function refreshTtl($key, $ttl = 3600)
    {
        return self::$data[$key]['exp'] = time() + $ttl;
    }

    public function add($key, $value, $ttl = 600)
    {
        if ($this->has($key)) {
            throw new CacheException('Key already present');
        }
        return $this->set($key, $value, $ttl);
    }
}