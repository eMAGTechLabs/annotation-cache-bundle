<?php


namespace CacheBundle\Tests;


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
}