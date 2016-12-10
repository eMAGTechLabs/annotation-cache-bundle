<?php

namespace eMAG\CacheBundle\Annotation;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
class Cache
{

    const STATE_DISABLED = 0;
    const STATE_ENABLED  = 1;
    const STATE_RESET    = 2;

    protected $cache;

    /**
     * @var string
     */
    protected $key = '';

    /**
     * @var int
     */
    protected $ttl = 600;

    /**
     * @var bool
     */
    protected $reset = false;

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @return mixed
     */
    public function getCache()
    {
        return $this->cache;
    }

    /**
     * Cache constructor.
     *
     * @param   array   $options
     */
    public function __construct(array $options)
    {
        $this->cache = $options['cache'];
        if (array_key_exists('key', $options)) {
            $this->key = $options['key'];
        }
        if (array_key_exists('ttl', $options)) {
            $this->ttl = (int)$options['ttl'];
        }
        if (array_key_exists('reset', $options)) {
            $this->reset = (bool)$options['reset'];
        }
    }

    /**
     * @return int
     */
    public function getTtl()
    {
        return $this->ttl;
    }

    /**
     * @return boolean
     */
    public function isReset()
    {
        return $this->reset;
    }
}

