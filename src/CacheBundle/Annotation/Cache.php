<?php

namespace CacheBundle\Annotation;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
class Cache
{
    /**
     * @var string
     */
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
     * @return string
     */
    public function getKey() : string
    {
        return $this->key;
    }

    /**
     * @return string
     */
    public function getCache() : string
    {
        return $this->cache;
    }

    /**
     * @return int
     */
    public function getTtl() : int
    {
        return $this->ttl;
    }

    /**
     * @return boolean
     */
    public function isReset() : bool
    {
        return $this->reset;
    }
}
