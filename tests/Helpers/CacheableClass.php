<?php


namespace CacheBundle\Tests\Helpers;


use CacheBundle\Annotation\Cache;

class CacheableClass
{
    /**
     * @Cache(cache="xxx", key="offset", ttl=30)
     * @param int $offset
     *
     * @return int
     */
    public function getCachedTime($offset = 0)
    {
        return rand(1 + $offset, microtime(true));
    }

    /**
     * @Cache(cache="xxx", key="offset", ttl=30, reset=true)
     * @param int $offset
     *
     * @return int
     */
    public function getCachedTimeWithReset($offset = 0)
    {
        return rand(1 + $offset, microtime(true));
    }

    /**
     * @Cache(cache="xxx2", key="", ttl=30)
     */
    public function getTimeWithoutParams()
    {

    }

    public function testWithoutCache()
    {
        return rand(1, microtime(true));
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
        return rand(1, microtime(true) + $param1 + $param2 + $param3);

    }

    /**
     * @Cache(cache="yyyy", key="param1, param3")
     * @param $param1
     * @param $param2
     * @param $param3
     *
     * @return int
     */
    public function testWithWrongParams($param1, $param2) : int
    {
        return rand(1, microtime(true) + $param1 + $param2);
    }

    /**
     * @Cache(cache="zzzz", key="param1, param3")
     * @param $param1
     * @param $param2
     * @param $param3
     *
     * @return int
     */
    public function testWithReturnType($param1, $param2) : int
    {
        return rand(1, microtime(true) + $param1 + $param2);
    }

    /**
     * @Cache(cache="yyzzzyy", key="")
     *
     * @return int
     */
    public function testWithoutParams()
    {
        return rand(1, microtime(true));
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
     * @Cache(cache="__key")
     *
     * @return int
     */
    public function getComputationOneWithoutParametersSamePrefix() : int
    {
        return 10;
    }

    /**
     * @Cache(cache="__key")
     *
     * @return int
     */
    public function getComputationTwoWithoutParametersSamePrefix() : int
    {
        return 20;
    }
}