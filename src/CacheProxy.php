<?php

namespace Anteris\Cache;

/**
 * Enables the caching of any class by proxying its methods through the cache
 * repository.
 */
class CacheProxy
{
    /** @var CacheRepository Handles all interaction with the cache. */
    private static CacheRepository $cache;

    /** @var object Any class that we want to make cacheable. */
    private static object $class;

    /** @var array Any methods that we want to ignore in the cache. */
    private static array $ignoreMethods;

    public function __construct(CacheRepository $cache, object $class, array $ignoreMethods = [])
    {
        static::$cache          = $cache;
        static::$class          = $class;
        static::$ignoreMethods  = $ignoreMethods;
    }

    /**
     * Handles interaction with non-static methods on the class.
     *
     * @param  string  $name  The method name
     * @param  array   $arguments  The methods arguments.
     *
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        return static::__callStatic($name, $arguments);
    }

    /**
     * Handles interaction with static methods on the class.
     *
     * @param  string  $name  The method name
     * @param  array   $arguments  The methods arguments.
     *
     * @return mixed
     */
    public static function __callStatic($name, $arguments)
    {
        if (in_array($name, static::$ignoreMethods)) {
            return call_user_func_array([static::$class, $name], $arguments);
        }

        $key = static::class . $name . serialize($arguments);

        return static::$cache->get($key, function () use ($name, $arguments) {
            return call_user_func_array([static::$class, $name], $arguments);
        });
    }

    /**
     * Handles getting of class properties (these are not cached).
     *
     * @param  string  $name  The property to be retrieved.
     */
    public function __get($name)
    {
        return static::$class->{$name};
    }

    /**
     * Handles the setting of class properties (these are not cached).
     *
     * @param  string  $name  The property to be set.
     * @param  mixed   $value The value to set the property to.
     */
    public function __set($name, $value)
    {
        static::$class->{$name} = $value;
    }
}
