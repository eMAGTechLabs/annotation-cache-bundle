<?php

declare(strict_types=1);

namespace EmagTechLabs\AnnotationCacheBundle\Tests\Functional\App\Service\Cacheable;

use EmagTechLabs\AnnotationCacheBundle\Annotation\Cache;

class CacheableMethod
{
    /**
     * @var int
     */
    protected $maxValue;

    public function __construct(int $maxValue)
    {
        $this->maxValue = $maxValue;
    }

    #[Cache(cache:'php8annotation', key:'offset', ttl:30)]
    public function getRandPHP8Annotation($offset = 0)
    {
        return rand(1 + $offset, $this->getIntMicrotime());
    }


    #[Cache(cache:'php8annotation', key:'offset', ttl:30, reset:true)]
    public function getRandPHP8AnnotationWithReset($offset = 0)
    {
        return rand(1 + $offset, $this->getIntMicrotime());
    }

    /**
     * @Cache(cache="xxx", key="offset", ttl=30)
     * @param int $offset
     *
     * @return int
     */
    public function getCachedTime($offset = 0)
    {
        return rand(1 + $offset, $this->getIntMicrotime());
    }

    /**
     * @return int
     */
    protected function getIntMicrotime(): int
    {
        return (int)microtime(true);
    }

    /**
     * @Cache(cache="xxx", key="offset", ttl=30, reset=true)
     * @param int $offset
     *
     * @return int
     */
    public function getCachedTimeWithReset($offset = 0)
    {
        return rand(1 + $offset, $this->getIntMicrotime());
    }

    /**
     * @Cache(cache="xxx2", key="", ttl=30)
     */
    public function getTimeWithoutParams()
    {
    }

    public function testWithoutCache()
    {
        return rand(1, $this->getIntMicrotime());
    }

    /**
     * @Cache(cache="yyyy", key="param1, param3")
     * @param $param1
     * @param $param2
     * @param $param3
     *
     * @return int
     */
    public function testWithMultipleParams($param1, $param2, $param3 = 100)
    {
        return rand(1, $this->getIntMicrotime() + $param1 + $param2 + $param3);
    }

    /**
     * @Cache(cache="yyyy", key="param1, param3")
     * @param $param1
     * @param $param2
     * @param $param3
     *
     * @return int
     */
    public function testWithWrongParams($param1, $param2): int
    {
        return rand(1, $this->getIntMicrotime() + $param1 + $param2);
    }

    /**
     * @Cache(cache="zzzz", key="param1, param3")
     * @param $param1
     * @param $param2
     * @param $param3
     *
     * @return int
     */
    public function testWithReturnType($param1, $param2): int
    {
        return rand(1, $this->getIntMicrotime() + $param1 + $param2);
    }

    /**
     * @Cache(cache="yyzzzyy", key="")
     *
     * @return int
     */
    public function testWithoutParams()
    {
        return rand(1, $this->getIntMicrotime());
    }

    public function publicMethodThatCallsProtected()
    {
        return $this->protectedMethod();
    }

    /**
     * @Cache(cache="protectedMethod", key="")
     * @param $param1
     * @param $param2
     * @param $param3
     *
     * @return int
     */
    protected function protectedMethod()
    {
        return time();
    }

    /**
     * @Cache(cache="__constructor", ttl=30)
     *
     * @return  int
     */
    public function getRandomInteger(): int
    {
        return rand(0, $this->maxValue);
    }

    /**
     * @Cache(cache="arrayCache", key="minAndMax", ttl=30)
     *
     * @param array $minAndMax
     *
     * @return  int
     */
    public function getResultFromArrayParameter(array $minAndMax): int
    {
        return rand($minAndMax[0], $minAndMax[1]);
    }
}
