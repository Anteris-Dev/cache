<?php

namespace Anteris\Cache\Tests;

use Anteris\Cache\CacheProxy;
use Anteris\Cache\CacheRepository;
use Anteris\Cache\Tests\TestNamespace\TestClass;
use PHPUnit\Framework\TestCase;

class CacheProxyTest extends TestCase
{
    private $cache;
    private $class;

    protected function setUp(): void
    {
        $this->cache = new CacheRepository(__DIR__);
        $this->class = new CacheProxy($this->cache, new TestClass, [
            'realtimeName',
        ]);
    }

    protected function tearDown(): void
    {
        if (is_dir(__DIR__ . '/.cache')) {
            $this->cache->clear();
            rmdir(__DIR__ . '/.cache');
        }
    }

    /**
     * @covers \Anteris\Cache\CacheProxy
     * @covers \Anteris\Cache\CacheRepository
     */
    public function test_it_proxies_non_static_method()
    {
        $this->assertEquals($this->class->getName(), 'Test Case');
    }

    /**
     * @covers \Anteris\Cache\CacheProxy
     * @covers \Anteris\Cache\CacheRepository
     */
    public function test_it_proxies_static_method()
    {
        $this->assertEquals($this->class::sayHello(), 'Hi!');
    }

    /**
     * @covers \Anteris\Cache\CacheProxy
     * @covers \Anteris\Cache\CacheRepository
     */
    public function test_it_does_not_proxy_ignored_methods()
    {
        $this->assertEquals($this->class->realtimeName(), 'Test Case');
        $this->class->firstName = 'Jim';
        $this->class->lastName  = 'Bob';
        $this->assertEquals($this->class->realtimeName(), 'Jim Bob');
    }

    /**
     * @covers \Anteris\Cache\CacheProxy
     * @covers \Anteris\Cache\CacheRepository
     */
    public function test_it_proxies_properties()
    {
        $this->assertEquals($this->class->firstName, 'Test');
        $this->assertEquals($this->class->lastName, 'Case');
        $this->assertEquals($this->class->getName(), 'Test Case');

        $this->class->firstName = 'Jim';
        $this->class->lastName  = 'Bob';

        $this->assertEquals($this->class->firstName, 'Jim');
        $this->assertEquals($this->class->lastName, 'Bob');

        // Make sure methods remain cached!
        $this->assertEquals($this->class->getName(), 'Test Case');
    }
}
