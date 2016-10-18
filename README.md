[![Build Status](https://travis-ci.org/eMAGTechLabs/cachebundle.svg?branch=master)](https://travis-ci.org/eMAGTechLabs/cachebundle)
[![Coverage Status](https://coveralls.io/repos/github/eMAGTechLabs/cachebundle/badge.svg?branch=master)](https://coveralls.io/github/eMAGTechLabs/cachebundle?branch=master)

In order to have caching on methods:

Add requirement:
    
    composer require emag/cache-bundle
    
Add to AppKernel at the top:

    new Go\Symfony\GoAopBundle\GoAopBundle(),
    new CacheBundle\CacheBundle(),

Configure the bundle required info

    services:
        cache.memory:
            class: \CacheBundle\Service\MemoryCache
        cache.redis:
            class: \CacheBundle\Service\RedisCache
            calls:
                - [ setRedis, [@<\Predis\Client>]]
        cache.memcached:
            class: \CacheBundle\Service\MemcachedCache
            calls:
                - [ setMemcachedClient, [@<\Memcached>]]



       cache.aspect:
           class: CacheBundle\DependencyInjection\CachingAspect
           calls:
              - [setLogger, ['@logger']]
              - [setCacheService, ['@cache.service']]
           tags:
               - { name: goaop.aspect }

Add @Cache  annotation to the methods to be cached

    @Cache(cache="some_sort_of_prefix", [key="<name of argument to include in cache key>"], [ttl=300], [reset=true])

