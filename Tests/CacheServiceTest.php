<?php
namespace CacheBundle\Tests;

use CacheBundle\Exception\CacheException;
use CacheBundle\Service\AbstractCache;
use CacheBundle\Service\ApcCache;
use CacheBundle\Service\CouchbaseCache;
use CacheBundle\Service\MemcachedCache;
use CacheBundle\Service\MemoryCache;
use CacheBundle\Service\MultiLevelCache;
use CacheBundle\Service\PsrCompatible;
use CacheBundle\Service\RedisCache;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

/**
 * Class CacheServiceTest
 */
class CacheServiceTest extends KernelTestCase
{

    public function cacheProvider()
    {
        $this->expectOutputString(null);

        $psr = new PsrCompatible();
        $psr->setBackend(new ArrayAdapter());
        return [
            [$psr, ['no-ttl']],
        ];
    }


    /**
     * @dataProvider cacheProvider
     * @expectedException \CacheBundle\Exception\CacheException
     * @expectedExceptionMessage already
     */
    public function testDoubleLock(AbstractCache $cacheService, $config = [])
    {
        if (in_array('no-lock', $config)) {
            throw new \CacheBundle\Exception\CacheException('already');
        }
        $this->cleanupBefore($cacheService);

        $cacheService->lock('test', 10);
        $cacheService->lock('test', 10);
    }

    /**
     * @dataProvider cacheProvider
     */
    public function testBasicCacheFunctionality(AbstractCache $cacheService, $config = [])
    {
        if (in_array('no-lock', $config)) {
            return true;
        }
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
        if (in_array('no-lock', $config)) {
            return true;
        }
        $this->cleanupBefore($cacheService);

        $cacheService->lock('test', 1);
        sleep(2);
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
        if (in_array('no-lock', $config)) {
            return true;
        }
        $this->cleanupBefore($cacheService);


        $cacheService->lock('test', 3);
        $cacheService->heartBeatLock('test', 130);
        if (in_array('no-ttl', $config)) {
            return true;
        }
        $this->assertGreaterThan(110, $cacheService->ttl(AbstractCache::LOCK_PREFIX . 'test'));
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
            $cacheService->unlock(AbstractCache::LOCK_PREFIX . 'test');
        } catch (CacheException $e) {

        }
        try {
            $cacheService->delete('test100');
        } catch (CacheException $e) {

        }
    }
}