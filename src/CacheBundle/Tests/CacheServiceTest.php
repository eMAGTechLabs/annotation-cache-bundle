<?php
namespace CacheBundle\Tests;

use CacheBundle\Exception\CacheException;
use CacheBundle\Service\AbstractCache;
use CacheBundle\Service\ApcCache;
use CacheBundle\Service\CouchbaseCache;
use CacheBundle\Service\MemcachedCache;
use CacheBundle\Service\MemoryCache;
use CacheBundle\Service\MultiLevelCache;
use CacheBundle\Service\RedisCache;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class CacheServiceTest
 */
class CacheServiceTest extends KernelTestCase
{

    public function cacheProvider()
    {
        $this->expectOutputString(null);
        //var_dump(getenv('TEST_REDIS_SERVER'));die;
        $memory = new MemoryCache();
        $redis = new RedisCache();
        $redis->setRedis(new \Predis\Client([
            'scheme' => 'tcp',
            'host' => getenv('TEST_REDIS_SERVER'),
        ]));
        $memcached = new MemcachedCache();
        $memConn = new \Memcached();
        $memConn->addServer(getenv('TEST_COUCHBASE_SERVER'), 11212);
        $memcached->setMemcachedClient($memConn);


        $multiLevel = new MultiLevelCache();
        $multiLevel->setEngines([
            $memory,
            $redis,
        ]);

        $couchbase = new \CouchbaseCluster('couchbase://' . getenv('TEST_COUCHBASE_SERVER'), 'Administrator', 'password');
        $bucket = $couchbase->openBucket(getenv('TEST_COUCHBASE_BUCKET'));
        $couchbaseLib = new CouchbaseCache();
        $couchbaseLib->setCouchBase($bucket);

        $apc = new ApcCache();

        return [
            [$apc, ['no-ttl']],
            [$memory],
            [$redis],
            [$multiLevel],
            [$memcached, ['no-ttl']],
            [$couchbaseLib, ['no-ttl']],
        ];
    }


    /**
     * @dataProvider cacheProvider
     * @expectedException \CacheBundle\Exception\CacheException
     * @expectedExceptionMessage already
     */
    public function testDoubleLock(AbstractCache $cacheService, $config = [])
    {
        $this->cleanupBefore($cacheService);

        $cacheService->lock('test', 10);
        $cacheService->lock('test', 10);
    }

    /**
     * @dataProvider cacheProvider
     */
    public function testBasicCacheFunctionality(AbstractCache $cacheService, $config = [])
    {
        $this->cleanupBefore($cacheService);
        $this->assertFalse($cacheService->has('test100'));
        $cacheService->add('test100', 300,5);
        $this->assertTrue($cacheService->has('test100'));
        $this->assertEquals(300, $cacheService->get('test100'));
        $cacheService->set('test100', 200,5);
        $this->assertEquals(200, $cacheService->get('test100'));
    }

    /**
     * @param AbstractCache $cacheService
     *
     * @dataProvider cacheProvider
     */
    public function testLockExpire(AbstractCache $cacheService, $config = [])
    {
        $this->cleanupBefore($cacheService);

        $cacheService->lock('test', 1);
        sleep(1);
        $cacheService->lock('test', 2);
        $this->assertEquals(true, $cacheService->hasLock('test'));
        sleep(3);
        $this->assertEquals(false, $cacheService->hasLock('test'));
    }


    /**
     * @param AbstractCache $cacheService
     *
     * @dataProvider cacheProvider
     */
    public function testLockExtend(AbstractCache $cacheService, $config = [])
    {

        $this->cleanupBefore($cacheService);


        $cacheService->lock('test', 3);
        $cacheService->heartBeatLock('test', 130);
        if (in_array('no-ttl', $config)) {
            return true;
        }
        $this->assertGreaterThan(110, $cacheService->ttl('lock:test'));
    }

    /**
     * @param AbstractCache $cacheService
     */
    protected function cleanupBefore(AbstractCache $cacheService)
    {
        try {
            $cacheService->unlock('test');
        } catch (CacheException $e) {

        }
        try {
            $cacheService->unlock('lock:test');
        } catch (CacheException $e) {

        }
        try {
            $cacheService->delete('test100');
        } catch (CacheException $e) {

        }
    }
}