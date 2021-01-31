<?php

declare(strict_types=1);

namespace EmagTechLabs\AnnotationCacheBundle\Annotation;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
class Cache
{
    public const STORAGE_LABEL_DEFAULT = 'default';
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
     * @var string
     */
    protected $storage = self::STORAGE_LABEL_DEFAULT;

    /**
     * Cache constructor.
     * @param array $options
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

        if (array_key_exists('storage', $options)) {
            $this->storage = (string)$options['storage'];
        }
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getCache(): string
    {
        return $this->cache;
    }

    public function getTtl(): int
    {
        return $this->ttl;
    }

    public function isReset(): bool
    {
        return $this->reset;
    }

    public function getStorage(): string
    {
        return $this->storage;
    }
}
