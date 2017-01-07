<?php

namespace CacheBundle\Tests;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Emag\CacheBundle\Annotation\CacheExpression;
use Emag\CacheBundle\Tests\Helpers\CacheableExpressionClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Kernel;

class CacheExpressionDefaultTest extends KernelTestCase
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    public function setUp()
    {
        parent::setUp();

        static::$class = null;
        self::bootKernel(['environment' => 'test_expr_lang_default']);
        $this->container = self::$kernel->getContainer();
    }

    protected static function getKernelClass()
    {
        return get_class(new class('test_expr_lang_default', []) extends Kernel
        {
            public function registerBundles()
            {
                return [
                    new \Emag\CacheBundle\EmagCacheBundle()
                ];
            }

            public function registerContainerConfiguration(LoaderInterface $loader)
            {
                $loader->load(__DIR__ . '/config_default_expression.yml');
            }

            public function __construct($environment, $debug)
            {
                parent::__construct($environment, $debug);

                $loader = require __DIR__ . '/../vendor/autoload.php';

                AnnotationRegistry::registerLoader(array($loader, 'loadClass'));
                $this->rootDir = __DIR__ . '/app/';
            }
        });
    }

    public function testDefaultExpressionLanguage()
    {
        /** @var CacheableExpressionClass $object */
        $object = $this->container->get('cache.expr.test.service');
        $methodName = 'getIntenseResult';
        $objectReflectionClass = new \ReflectionClass($object);
        $annotationReader = $this->container->get('annotation_reader');
        /** @var CacheExpression $cacheExpressionAnnotation */
        $cacheExpressionAnnotation = $annotationReader->getMethodAnnotation(new \ReflectionMethod($objectReflectionClass->getParentClass()->getName(), $methodName), CacheExpression::class);
        $cacheExpressionAnnotation
            ->setExpressionLanguage($this->container->get('emag.cache.expression.language'))
            ->setContext($object)
        ;

        $result = $object->$methodName();
        $this->assertContains($object->buildCachePrefix(), $cacheExpressionAnnotation->getCache());
        $this->assertEquals(0, strpos($cacheExpressionAnnotation->getCache(), $object->buildCachePrefix()));
        $this->assertEquals($result, $object->$methodName());
    }

    public function tearDown()
    {
        static::$class = null;
    }
}
