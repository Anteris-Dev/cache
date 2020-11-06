<?php

namespace Anteris\Cache\Tests;

use Anteris\Cache\CacheRepository;
use PHPUnit\Framework\TestCase;

class CacheRepositoryTest extends TestCase
{
    private CacheRepository $cache;

    protected function setUp(): void
    {
        $this->cache = new CacheRepository(__DIR__);
    }

    protected function tearDown(): void
    {
        if (is_dir(__DIR__ . '/.cache')) {
            $this->cache->clear();
            rmdir(__DIR__ . '/.cache');
        }

        if (is_dir(__DIR__ . '/.test')) {
            $this->cache->clear();
            rmdir(__DIR__ . '/.test');
        }
    }

    /**
     * @covers \Anteris\Cache\CacheRepository
     */
    public function test_it_can_set_cache_items()
    {
        $key        = 'test';
        $md5        = md5($key);
        $content    = 'testing the cache';
        $dir        = __DIR__ . '/.cache';
        $file       = "$dir/$md5";

        // Pre-run assertions
        $this->assertDirectoryDoesNotExist($dir);
        $this->assertFileDoesNotExist($file);

        // Run
        $this->cache->set($key, $content);

        // Post-run assertions
        $this->assertDirectoryExists($dir);
        $this->assertFileExists($file);
        $this->assertEquals(serialize($content), file_get_contents($file));
    }

    /**
     * @covers \Anteris\Cache\CacheRepository
     */
    public function test_it_can_get_cache_items()
    {
        $key        = 'testing123';
        $content    = 'cool!';

        $this->assertEquals($this->cache->get($key), null);

        $this->cache->set($key, $content);

        $this->assertEquals($this->cache->get($key), $content);
    }

    /**
     * @covers \Anteris\Cache\CacheRepository
     */
    public function test_it_can_get_callable_defaults()
    {
        $key        = 'testing123';
        $content    = 'cool!';

        $this->assertEquals($this->cache->get($key), null);

        $result = $this->cache->get($key, function () use ($content) {
            return $content;
        });

        $this->assertEquals($result, $content);
        $this->assertEquals($this->cache->get($key), $content);
    }

    /**
     * @covers \Anteris\Cache\CacheRepository
     */
    public function test_it_can_check_if_item_exists()
    {
        $key = 'newtest';
        $this->assertFalse($this->cache->has($key));
        $this->cache->set($key, 'content');
        $this->assertTrue($this->cache->has($key));
    }

    /**
     * @covers \Anteris\Cache\CacheRepository
     */
    public function test_it_can_get_the_cache_directory()
    {
        $this->assertEquals($this->cache->getCacheDirectory(), '.cache');
    }

    /**
     * @covers \Anteris\Cache\CacheRepository
     */
    public function test_it_can_update_the_cache_directory_and_write_to_it()
    {
        $key        = 'testing';
        $md5        = md5($key);
        $content    = 'new test';

        // Test updating the directory
        $this->assertDirectoryDoesNotExist(__DIR__ . '/.test');
        $this->assertFileDoesNotExist(__DIR__ . '/test/' . $md5);
        $this->cache->setCacheDirectory('.test');
        $this->assertEquals($this->cache->getCacheDirectory(), '.test');

        // Test writing to it
        $this->cache->set($key, $content);
        $this->assertDirectoryExists(__DIR__ . '/.test');
        $this->assertFileExists(__DIR__ . '/.test/' . $md5);
        $this->assertEquals($content, unserialize(file_get_contents(__DIR__ . '/.test/' . $md5)));
    }

    /**
     * @covers \Anteris\Cache\CacheRepository
     */
    public function test_it_can_remove_subdirectories()
    {
        mkdir(__DIR__ . '/.cache');
        mkdir(__DIR__ . '/.cache/testing');
        
        $this->assertDirectoryExists(__DIR__ . '/.cache');
        $this->assertDirectoryExists(__DIR__ . '/.cache/testing');

        $this->cache->clear();

        $this->assertDirectoryDoesNotExist(__DIR__ . '/.cache/testing');
    }
}
