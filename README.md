# Annotation Cache Bundle
[![Packagist Version](https://img.shields.io/packagist/v/emag-tech-labs/annotation-cache-bundle)][package]
[![Build Status](https://travis-ci.com/eMAGTechLabs/annotation-cache-bundle.svg?branch=master)][travis]
[![Total Downloads](https://poser.pugx.org/emag-tech-labs/annotation-cache-bundle/downloads)][package]
[![Latest Stable Version](https://poser.pugx.org/emag-tech-labs/annotation-cache-bundle/v/stable)][package]
[![License](https://poser.pugx.org/emag-tech-labs/annotation-cache-bundle/license)][license]
[![Coverage Status](https://coveralls.io/repos/github/eMAGTechLabs/annotation-cache-bundle/badge.svg?branch=master)][coveralls]

----
Annotation based caching for method responses in services inside a Symfony container.

This bundle helps you to add caching with a simple annotation or attribute. You can store the cache in any class that 
implements PSR-6: Caching Interface be it a simple array or a redis/memcache storage.

## How it works
This bundle will scan all the methods from the defined services and look for the Cache annotation or attribute (for PHP 8+). 

For all the services where the Cache annotation/attribute is found, it will create a proxy class (using ocramius/proxy-manager) 
that extends the service class, include the CacheableClassTrait and overwrite the methods that have the Cache annotation/attribute.

The overwritten methods consist of a call to the `getCached` method that identifies the annotation details, gets the key,
gets the Cache PSR-6 implementation and then gets the result from cache. If no data with the generated cache key is found, 
it will call the original method and then save the response in the given provider.

The bundle has a CompilerPass implementation that will search and overwrite the service definition with the proxy class
created in the process explained above.

## Installation

### With Symfony Flex
The easiest way to install and configure the AnnotationCacheBundle with Symfony is by using
[Symfony Flex](https://github.com/symfony/flex):

```bash
 composer require symfony/flex ^1.0
 composer config extra.symfony.allow-contrib true
 composer require emag-tech-labs/annotation-cache-bundle
```

Symfony Flex will automatically register and configure the bundle.

### Without Symfony Flex
If your application does not use Symfony Flex you can configure the bundle manually by following the steps below

#### Step 1: Download the bundle
Open a command console, enter your project directory and execute the following command to download the latest stable 
version of this bundle:

```console
composer require emag-tech-labs/annotation-cache-bundle
```

#### Step 2: Enable the bundle
Then, enable the bundle by adding it to the list of registered bundles in the `config/bundles.php` file of your project:

```php
// config/bundles.php

return [
    // ...
    EmagTechLabs\AnnotationCacheBundle\AnnotationCacheBundle::class => ['all' => true],
];
```

#### Step 3: Configuration
The easiest way is to dump the config and copy it to `configs/packages/emag_annotation_cache.yaml`.

```console
bin/console config:dump-reference AnnotationCacheBundle
```

##### Configuration example
You have to configure the name of the service that is PSR6 compliant, that means it will have to implement 
`Psr\Cache\CacheItemPoolInterface`:

```yaml
# app/config/services.yaml

services:
    cache.array:
        class: Symfony\Component\Cache\Adapter\ArrayAdapter
    cache.redis:
        class: Symfony\Component\Cache\Adapter\RedisAdapter
        arguments: ['@predis']
```

```yaml
#configs/packages/emag_annotation_cache.yaml

# Annotation Cache Bundle
annotation_cache:
    provider: 
        default: cache.redis
        array: cache.array
    ignore_namespaces:
      - 'Symfony\\'
      - 'Doctrine\\'
```

## Usage
Add the `Cache` annotation for the methods you want to cache.

Annotation parameters:
- `cache` - cache prefix, __string__, _default value: null_ (eg: 'my_custom_prefix_')
- `key` - name of argument(s) to include in cache key hash generation, __string, arg name separated by a comma__, 
  _default value: ''_ (eg: 'a,b')
- `ttl` - time to store the cache in seconds, __int__, _default value: 600_ (eg: '3600')
- `reset` - if the cache should be reset or not, __boolean__, _default value: false_ (eg: 'true')
- `storage` - if multiple providers are defined, you can specify which provider to use, __string__, 
  _default value: 'default'_ (eg: 'array')


### Annotations defined with Doctrine Annotations library
```php
use EmagTechLabs\AnnotationCacheBundle\Annotation\Cache;

/**
 * @Cache(cache="<put your prefix>", [key="<name of argument to include in cache key separated by comma>",  [ttl=600, [reset=true, [storage=default ]]]])
 */
```
#### Example

```php
namespace AppCacheBundle\Service;

use EmagTechLabs\AnnotationCacheBundle\Annotation\Cache;

class AppService
{
    /**
     * @Cache(cache="app_high_cpu", ttl=60)
     *
     * @return int
     */
    public function getHighCPUOperation(): int
    {
        sleep(10); // 'Simulate a time consuming operation';
        return 20;
    }
}
```

### Annotations defined with PHP 8 attributes
```php
use EmagTechLabs\AnnotationCacheBundle\Annotation\Cache;

 #[Cache(cache:'<put your prefix>', key:'<name of argument to include in cache key separated by comma>', ttl:600, reset: true, storage: 'default')]
```
#### Example

```php
namespace AppCacheBundle\Service;

use EmagTechLabs\AnnotationCacheBundle\Annotation\Cache;

class AppService
{
    #[Cache(cache:'app_high_cpu', ttl: 60)]
    public function getHighCPUOperation(): int
    {
        sleep(10); // 'Simulate a time consuming operation';
        return 20;
    }
}
```

### Use cases
Below you can find two ways you can use this Bundle

#### Config
```yaml
# app/config/services.yaml

services:
    cache.array:
        class: Symfony\Component\Cache\Adapter\ArrayAdapter
    cache.redis:
        class: Symfony\Component\Cache\Adapter\RedisAdapter
        arguments: ['@predis']
```

```yaml
#configs/packages/emag_annotation_cache.yaml

# Annotation Cache Bundle
annotation_cache:
    provider: 
        default: cache.array
        redis: cache.redis
    ignore_namespaces:
      - 'Symfony\\'
```

#### Service Code
This bundle can be used in multiple ways, two of them are shown below.

The first case is the most common one, where you have a method that does multiple time-consuming operations, and you want
to cache the response in redis with a prefix (simple_time_consuming_operation_) for a given time (60s in the case below).
The logic here is to look for the value in redis, and if not found, run the actual method, get the result and cache it
for further use taking into account the arguments passed in the `@Cache` annotation.

The second case could be used if you want to generate the cache in a command and have it already warmed up, or maybe 
update the cache when a certain event is triggered, or you update the information in a database. Based on the example below, 
you could set a cron to run every ~3000 seconds and that will recreate the cache before it expires. Because we are using 
the same cache prefix and keys, when the same argument values are passed for both methods(`getTimeConsumingOperationValueWithReset` 
and `getTimeConsumingOperationValue`), the generated cache key will be the same, in this case: 
time_consuming_operation_7fe49b314fb356bee76dbd3b8716b4d5ab5db600.
That means that both methods will write (and read) the cache in (from) the same cache key. Because the second method has 
the `reset` argument set to true, any call to the second method will overwrite the cache value in the key
time_consuming_operation_7fe49b314fb356bee76dbd3b8716b4d5ab5db600 with the new result of the function.


```php
namespace AppCacheBundle\Service;

use EmagTechLabs\AnnotationCacheBundle\Annotation\Cache;

class AppService
{
    /**
     * @Cache(cache="simple_time_consuming_operation_", ttl=60, storage="redis")
     *
     * @param int $a
     * @param int $b
     * 
     * @return int
     */
    public function getSimpleTimeConsumingOperationValue(int $a, int $b): int
    {
        sleep(10); // 'Simulate a time consuming operation';
        return $a + $b;
    }
    
    #[Cache(cache:'time_consuming_operation_', key: 'a,b', ttl: 3600, storage: 'redis')]
    public function getTimeConsumingOperationValue(int $a, int $b): int
    {
        return $this->getTimeConsumingOperationValueWithReset($a, $b);
    }
    
    #[Cache(cache:'time_consuming_operation_', key: 'a,b', ttl: 3600, reset: true, storage: 'redis')]
    public function getTimeConsumingOperationValueWithReset(int $a, int $b): int
    {
        sleep(10); // 'Simulate a time consuming operation';
        return $a + $b;
    }
}
```

#### Service calls
```php
// from controller
/** AppService $appService */
$appService->getTimeConsumingOperationValue(1, 2);

// from command
/** AppService $appService */
$appService->getTimeConsumingOperationValueWithReset(1, 2);
```


## Contributing
Thanks for your interest in contributing! There are many ways to contribute to this project. Get started [here](CONTRIBUTING.md).


[license]: https://github.com/emag-tech-labs/annotation-cache-bundle/blob/master/LICENSE
[package]: https://packagist.org/packages/emag-tech-labs/annotation-cache-bundle
[travis]: https://travis-ci.com/eMAGTechLabs/annotation-cache-bundle
[coveralls]: https://coveralls.io/github/eMAGTechLabs/annotation-cache-bundle?branch=master