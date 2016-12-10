<?php


namespace eMAG\CacheBundle\Service;

use eMAG\CacheBundle\Exception\CacheException;

class MultiLevelCache extends AbstractCache
{
    /**
     * @var AbstractCache[]
     */
    protected $engines;

    /**
     * @param AbstractCache[] $engines
     */
    public function setEngines(array $engines)
    {
        $this->engines = $engines;
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
        $ttl = $this->explodeTtl($ttl);

        foreach ($this->engines as $alias => $engine) {
            $engine->set($key, $value, $ttl[$alias]);
        }
    }

    /**
     * Checks if a cache key exists
     *
     * @param $key
     *
     * @return bool
     */
    public function has($key)
    {
        foreach ($this->engines as $engine) {
            if ($engine->has($key)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Retrieves info from a cache key
     *
     * @param $key
     *
     * @return mixed
     */
    public function get($key)
    {
        foreach ($this->engines as $engine) {
            $data = $engine->get($key);
            if ($data) {
                return $data;
            }
        }

        return false;
    }

    /**
     * Retrieves the ttl of the specified cache key
     *
     * @param $key
     *
     * @return int
     */
    public function ttl($key)
    {
        $max = 0;
        foreach ($this->engines as $engine) {
            $max = max($max, $engine->ttl($key));
        }
        return $max;
    }

    /**
     * Deletes the cache key. If key does not exists, it will throw CacheException
     *
     * @param $key
     *
     * @return mixed
     * @throws CacheException
     */
    public function delete($key)
    {
        foreach ($this->engines as $engine) {
            $engine->delete($key);
        }
    }

    /**
     * Increases the ttl of the cache
     *
     * @param     $key
     * @param int $ttl
     */
    public function refreshTtl($key, $ttl = 3600)
    {
        $ttl = $this->explodeTtl($ttl);

        foreach ($this->engines as $alias => $engine) {
            $engine->refreshTtl($key, $ttl[$alias]);
        }
    }

    /**
     * @param $ttl
     *
     * @return array
     */
    protected function explodeTtl($ttl)
    {
        if (is_numeric($ttl)) {
            $ttl = array_combine(array_keys($this->engines), array_fill(0, count($this->engines), $ttl));

            return $ttl;
        }

        return $ttl;
    }

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
        $ttl = $this->explodeTtl($ttl);

        foreach ($this->engines as $alias => $engine) {
            $engine->add($key, $value, $ttl[$alias]);
        }
    }
}