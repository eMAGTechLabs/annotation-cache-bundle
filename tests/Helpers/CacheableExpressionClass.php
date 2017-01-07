<?php

namespace Emag\CacheBundle\Tests\Helpers;

use Emag\CacheBundle\Annotation as eMAG;

class CacheableExpressionClass
{
    /** @var  string */
    private $prefix;

    public function __construct(string $prefix)
    {
        $this->prefix = $prefix;
    }

    /**
     * @eMAG\CacheExpression(cache="this.buildCachePrefix()")
     *
     * @return  int
     */
    public function getIntenseResult() : int
    {
        return rand();
    }

    /**
     * @return string
     */
    public function buildCachePrefix() : string
    {
        return sprintf('_expr[%s]', $this->prefix);
    }
}
