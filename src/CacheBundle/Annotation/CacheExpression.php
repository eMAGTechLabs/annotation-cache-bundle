<?php

declare(strict_types=1);

namespace CacheBundle\Annotation;

use CacheBundle\Exception\CacheException;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
class CacheExpression extends Cache
{
    /** @var  ExpressionLanguage */
    protected $expressionLanguage;

    /**
     * @inheritDoc
     */
    public function getCache() : string
    {
        $this->getExpressionLanguage()->evaluate($this->cache, ['this' => $this]);

        return '';
    }

    /**
     * @return  ExpressionLanguage
     *
     * @throws  CacheException
     */
    private function getExpressionLanguage()
    {
        if (null === $this->expressionLanguage) {
            if (!class_exists('Symfony\Component\ExpressionLanguage\ExpressionLanguage')) {
                throw new CacheException('Unable to use expressions as the Symfony ExpressionLanguage component is not installed.');
            }

            $this->expressionLanguage = new ExpressionLanguage();
        }

        return $this->expressionLanguage;
    }
}