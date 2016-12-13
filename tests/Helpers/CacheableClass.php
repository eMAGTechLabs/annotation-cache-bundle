<?php

namespace CacheBundle\Tests\Helpers;

use CacheBundle\Annotation\Cache;

class CacheableClass
{
    /**
     * @Cache(cache="xxx", key="offset", ttl=30)
     *
     * @param   int     $offset
     *
     * @return  int
     */
    public function getCachedTime($offset = 0) : int
    {
        return rand(1 + $offset, microtime(true));
    }

    /**
     * @Cache(cache="xxx", key="offset", ttl=30, reset=true)
     *
     * @param   int     $offset
     *
     * @return  int
     */
    public function getCachedTimeWithReset($offset = 0) : int
    {
        return rand(1 + $offset, microtime(true));
    }

    /**
     * @Cache(cache="xxx2", key="", ttl=30)
     *
     * @return  void
     */
    public function getTimeWithoutParams()
    {

    }

    /**
     * @return int
     */
    public function testWithoutCache() : int
    {
        return rand(1, microtime(true));
    }

    /**
     * @Cache(cache="yyyy", key="param1, param3")
     *
     * @param $param1
     * @param $param2
     * @param $param3
     *
     * @return int
     */
    public function testWithMultipleParams($param1, $param2, $param3 = 100) : int
    {
        return rand(1, microtime(true) + $param1 + $param2 + $param3);

    }

    /**
     * @Cache(cache="yyyy", key="param1, param3")
     *
     * @param $param1
     * @param $param2
     *
     * @return int
     */
    public function testWithWrongParams($param1, $param2) : int
    {
        return rand(1, microtime(true) + $param1 + $param2);
    }

    /**
     * @Cache(cache="zzzz", key="param1, param3")
     *
     * @param $param1
     * @param $param2
     *
     * @return int
     */
    public function testWithReturnType($param1, $param2) : int
    {
        return rand(1, microtime(true) + $param1 + $param2);
    }

    /**
     * @Cache(cache="yyzzzyy")
     *
     * @return int
     */
    public function testWithoutParams() : int
    {
        return rand(1, microtime(true));
    }

    /**
     * @return int
     */
    public function publicMethodThatCallsProtected() : int
    {
        return $this->protectedMethod();
    }

    /**
     * @Cache(cache="protectedMethod")
     *
     * @return int
     */
    protected function protectedMethod() : int
    {
        return time();
    }
}
