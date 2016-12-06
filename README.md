[![Build Status](https://travis-ci.org/eMAGTechLabs/cachebundle.svg?branch=master)](https://travis-ci.org/eMAGTechLabs/cachebundle)
[![Coverage Status](https://coveralls.io/repos/github/eMAGTechLabs/cachebundle/badge.svg?branch=master)](https://coveralls.io/github/eMAGTechLabs/cachebundle?branch=master)

In order to have caching on methods:

Add requirement:
    
    composer require emag/cache-bundle
    
Add to AppKernel
    
    new CacheBundle\CacheBundle(),
    new JMS\AopBundle\JMSAopBundle(),

Configure the bundle required info


    jms_aop:
        cache_dir: %kernel.cache_dir%/jms_aop
    parameters:
        cache.service: cache.<select your engine>
    services:
       cache.array:
          class: Symfony\Component\Cache\Adapter\ArrayAdapter
        cache.redis:
          class: Symfony\Component\Cache\Adapter\RedisAdapter

Add @Cache  annotation to the methods to be cached


    @Cache(cache="some_sort_of_prefix", [key="<name of argument to include in cache key>"], [ttl=300], [reset=true])
    
    
Also there is a generic caching service under "targeting.cache"
