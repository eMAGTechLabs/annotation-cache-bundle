<?php

declare(strict_types=1);

namespace Emag\CacheBundle\Annotation;

use Emag\CacheBundle\Exception\CacheException;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
class CacheExpression extends Cache
{
    /**
     * @var ExpressionLanguage
     */
    protected $expressionLanguage;

    /**
     * @var object
     */
    private $context;

    /**
     * @var bool
     */
    private $hasEvaluation = false;

    /**
     * @inheritDoc
     */
    public function getCache() : string
    {
        if (!$this->hasEvaluation) {
            $this->cache = $this->getExpressionLanguage()->evaluate($this->cache, ['this' => $this->context]);
            $this->hasEvaluation = true;
        }

        return $this->cache;
    }

    /**
     * @param   object  $context
     *
     * @return  CacheExpression
     */
    public function setContext($context) : self
    {
        $this->context = $context;

        return $this;
    }

    /**
     * @return  ExpressionLanguage
     *
     * @throws  CacheException
     */
    private function getExpressionLanguage() : ExpressionLanguage
    {
        if (null === $this->expressionLanguage) {
            if (!class_exists('Symfony\Component\ExpressionLanguage\ExpressionLanguage')) {
                throw new CacheException('Unable to use expressions as the Symfony ExpressionLanguage component is not installed.');
            }

            $this->expressionLanguage = new ExpressionLanguage(new FilesystemAdapter('expr_cache'));
        }

        return $this->expressionLanguage;
    }
}