<?php

declare(strict_types=1);

namespace Emag\CacheBundle\Annotation;

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
            $this->cache = $this->expressionLanguage->evaluate($this->cache, ['this' => $this->context]);
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
     * @param   ExpressionLanguage  $language
     *
     * @return  CacheExpression
     */
    public function setExpressionLanguage(ExpressionLanguage $language) : self
    {
        $this->expressionLanguage = $language;

        return $this;
    }
}
