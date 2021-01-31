<?php
// phpcs:ignoreFile
declare(strict_types=1);

namespace EmagTechLabs\AnnotationCacheBundle\Annotation;

use Attribute;
use TypeError;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
class Cache
{
    public const STORAGE_LABEL_DEFAULT = 'default';
    public const DEFAULT_CACHE_TTL = 600;

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
    protected $ttl = self::DEFAULT_CACHE_TTL;

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
     * @SuppressWarnings(PHPMD)
     *
     * @param string|array $data
     * @param string|null $cache
     * @param string $key
     * @param int $ttl
     * @param bool $reset
     * @param string $storage
     */
    public function __construct(
        $data = [],
        string $cache = null,
        string $key = '',
        int $ttl = self::DEFAULT_CACHE_TTL,
        bool $reset = false,
        string $storage = self::STORAGE_LABEL_DEFAULT
    ) {
        if (is_string($data)) {
            $data = ['cache' => $data];
        } elseif (!is_array($data)) {
            throw new TypeError(
                sprintf(
                    '"%s": Argument $data is expected to be a string or array, got "%s".',
                    __METHOD__,
                    get_debug_type($data)
                )
            );
        }

        $this->cache = $data['cache'] ?? $cache;
        $this->key = $data['key'] ?? $key;
        $this->ttl = $data['ttl'] ?? $ttl;
        $this->reset = $data['reset'] ?? $reset;
        $this->storage = $data['storage'] ?? $storage;
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
