# Annotation Cache Bundle
[![Packagist Version](https://img.shields.io/packagist/v/emag-tech-labs/annotation-cache-bundle)][package]
[![Build Status](https://travis-ci.com/eMAGTechLabs/annotation-cache-bundle.svg?branch=master)][travis]
[![Total Downloads](https://poser.pugx.org/emag-tech-labs/annotation-cache-bundle/downloads)][package]
[![Latest Stable Version](https://poser.pugx.org/emag-tech-labs/annotation-cache-bundle/v/stable)][package]
[![License](https://poser.pugx.org/emag-tech-labs/annotation-cache-bundle/license)][license]
[![Coverage Status](https://coveralls.io/repos/github/eMAGTechLabs/annotation-cache-bundle/badge.svg?branch=master)][coveralls]

----
Annotation based caching for services inside a symfony container

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

### Annotations defined with Doctrine Annotations library
```php
use EmagTechLabs\AnnotationCacheBundle\Annotation\Cache;

/**
 * @Cache(cache="<put your prefix>", [key="<name of argument to include in cache key separated by comma>",  [ttl=600, [reset=true ]]])
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

 #[Cache(cache:'<put your prefix>', key:'<name of argument to include in cache key separated by comma>', ttl:600, reset: true)]
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

## Contributing
Thanks for your interest in contributing! There are many ways to contribute to this project. Get started [here](CONTRIBUTING.md).


[license]: https://github.com/emag-tech-labs/annotation-cache-bundle/blob/master/LICENSE
[package]: https://packagist.org/packages/emag-tech-labs/annotation-cache-bundle
[travis]: https://travis-ci.com/eMAGTechLabs/annotation-cache-bundle
[coveralls]: https://coveralls.io/github/eMAGTechLabs/annotation-cache-bundle?branch=master