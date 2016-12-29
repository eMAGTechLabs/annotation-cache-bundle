eMAG CachingBundle [![SensioLabsInsight](https://insight.sensiolabs.com/projects/04ea73ef-649e-449e-b36b-3b44dc98a9f1/mini.png)](https://insight.sensiolabs.com/projects/04ea73ef-649e-449e-b36b-3b44dc98a9f1) [![Build Status](https://travis-ci.org/eMAGTechLabs/cachebundle.svg?branch=master)](https://travis-ci.org/eMAGTechLabs/cachebundle)  [![Coverage Status](https://coveralls.io/repos/github/eMAGTechLabs/cachebundle/badge.svg?branch=master)](https://coveralls.io/github/eMAGTechLabs/cachebundle?branch=master)
----

## Installation

In order to have caching on methods you need to install it using composer:

1. Add requirement:
    
```bash
   $ composer require emag/cache-bundle
```
    
2. Add to your app/AppKernel.php
    
```php

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = [
            //...
            
            new Emag\CacheBundle\CacheBundle(),
            
            //...
        ];
        
        //...
    }
 
    //....
}
```

3. Configure the bundle required info

You have to configure the name of the service that is PSR6 compliant, that means it will have to implement `Psr\Cache\CacheItemPoolInterface`:

```yml
    # app/config/services.yml
    
    services:
        cache.array:
            class: Symfony\Component\Cache\Adapter\ArrayAdapter
            
        cache.redis:
            class: Symfony\Component\Cache\Adapter\RedisAdapter
            arguments: ['@predis']
```

```yml
    #app/config/config.yml
    
    # eMAG CachingBundle
    emag_cache:
        provider: cache.redis
        ignore_namespaces:
          - 'Symfony\\'
          - 'Doctrine\\'
          - 'Twig_'
          - 'Monolog\\'
          - 'Swift_'
          - 'Sensio\\Bundle\\'
```

## How to use

Add @Cache annotation to the methods you want to be cached:


```php
    
    use Emag\CacheBundle\Annotation\Cache;
    
   /**
     * @Cache(cache="<put your prefix>", [key="<name of argument to include in cache key separated by comma>",  [ttl=600, [reset=true ]]])
     */
```

Here is an example from a service:

```php
    
    namespace AppCacheBundle\Service;
    
    use Emag\CacheBundle\Annotation\Cache;
    
    class AppService
    {
        
        /**
         * @Cache(cache="app_high_cpu", ttl=60)
         *
         * @return int
         */
        public function getHighCPUOperation()
        {
            // 'Simulate a time consuming operation';
            
            sleep(10);
    
            return 20;
        }
    }
```

## Want to contribute?

Submit a PR and join the fun.


Enjoy!
