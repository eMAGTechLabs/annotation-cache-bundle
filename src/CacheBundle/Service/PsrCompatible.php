<?php

namespace CacheBundle\Service;

use CacheBundle\Exception\CacheException;
use Psr\Cache\CacheItemPoolInterface;

class PsrCompatible extends AbstractCache
{
    /**
     * @var CacheItemPoolInterface
     */
    protected $backend;

    /**
     * @inheritDoc
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
     * @inheritDoc
     */
    public function set($key, $value, $ttl = 600)
    {
        $item = $this->backend->getItem($key);
        $item->set($value);
        $item->expiresAfter($ttl);
        $this->backend->save($item);
    }

    /**
     * @inheritDoc
     */
    public function has($key)
    {
        return $this->backend->hasItem($key);
    }

    /**
     * @inheritDoc
     */
    public function get($key)
    {
        return $this->backend->getItem($key)->get();
    }

    /**
     * @inheritDoc
     */
    public function ttl($key)
    {
        throw new CacheException('Unable to determine TTL!');
    }

    /**
     * @inheritDoc
     */
    public function delete($key)
    {
        $this->backend->deleteItem($key);
    }

    /**
     * @inheritDoc
     */
    public function refreshTtl($key, $ttl = 3600)
    {
        $item = $this->backend->getItem($key)->expiresAfter($ttl);
        $this->backend->save($item);
    }

    /**
     * @param   CacheItemPoolInterface $backend
     *
     * @return  void
     */
    public function setBackend(CacheItemPoolInterface $backend)
    {
        $this->backend = $backend;
    }
}
