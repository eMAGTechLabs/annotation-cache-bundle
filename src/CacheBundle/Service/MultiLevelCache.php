<?php

namespace CacheBundle\Service;

use CacheBundle\Exception\CacheException;

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
     * @inheritDoc
     */
    public function set($key, $value, $ttl = 600)
    {
        $ttl = $this->explodeTtl($ttl);

        foreach ($this->engines as $alias => $engine) {
            $engine->set($key, $value, $ttl[$alias]);
        }
    }

    /**
     * @inheritDoc
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
     * @inheritDoc
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
     * @inheritDoc
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
     * @inheritDoc
     */
    public function delete($key)
    {
        foreach ($this->engines as $engine) {
            $engine->delete($key);
        }
    }

    /**
     * @inheritDoc
     */
    public function refreshTtl($key, $ttl = 3600)
    {
        $ttl = $this->explodeTtl($ttl);

        foreach ($this->engines as $alias => $engine) {
            $engine->refreshTtl($key, $ttl[$alias]);
        }
    }

    /**
     * @param   int|array $ttl
     *
     * @return  array
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
     * @inheritDoc
     */
    public function add($key, $value, $ttl = 600)
    {
        $ttl = $this->explodeTtl($ttl);

        foreach ($this->engines as $alias => $engine) {
            $engine->add($key, $value, $ttl[$alias]);
        }
    }
}
