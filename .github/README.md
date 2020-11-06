# The Blazing Fast Cache Manager
This zero-dependency cache manager was built for speed.

# To Install
```bash
composer require anteris-dev/cache
```

# Getting Started

## Creating an Instance
When instantiating the cache repository, you will be given the option to pass a base directory to the class (by default it will use the current working directory). The cache repository will create a directory named `.cache` in this base directory. This cache directory can be customized by calling the `setCacheDirectory()` method.

Example:

```php

// Creates an instance of the cache repository in our current directory. Cache
// files will be store in "./.cache"
$cache = new \Anteris\Cache\CacheRepository;

// Creates an instance of the cache repository in a sub directory. Cache files
// will be stored in "./sub/.mycache"
$cache = new \Anteris\Cache\CacheRepository(__DIR__ . '/sub');
$cache->setCacheDirectory('.mycache');

```

## Reading and Writing to the Cache
There are three useful methods for reading from and writing to the cache. These are `get()`, `has()`, and `set()`. An example of their basic use can be found below.

Example:

```php

// Echos "anteris-dev/cache"
$cache->set('package', 'anteris-dev/cache');

if ($cache->has('package')) {
    echo $cache->get('package');
}

```

The `get()` method allows you to pass a second parameter which defines its default value if the key passed is not found in the cache. If this second parameter is a callback function, it will be executed, its result will be cached, and its return value will be returned. Examples of these powerful features can be seen below.

```php

// returns "null"
$cache->get('some-unset-key');

// returns "Whoops! I'm not set!"
$cache->get('some-unset-key', "Whoops! I'm not set!");

// returns "false"
$cache->get('some-unset-key', false);

// returns "94"
$int = 50;
$cache->get('some-unset-key', function () use ($int) {
    return (($int - 3) * 2);
});

// now returns "94", but from cache
echo $cache->get('some-unset-key');

```

## Clearing the Cache
The cache can be cleared by calling the `clear()` method.

Example:

```php

$cache->set('key', 'Hi there!');

$cache->clear();

// Echos "null"
echo $cache->get('key');

```

# Cache Proxy
This package ships with a handy helper class which will cache the result of every method call to your own class. This is done by wrapping your class in our cache proxy class. The first parameter to this class must be an instance of `Anteris\Cache\CacheRepository`, the second parameter must be an instance of your own class. An option third paramter can be passed an array of methods you would not like to cache the results of.

Below you will find a quick demonstration of how to use this.

```php

class MyClass
{
    public $firstName = 'Test';
    public $lastName  = 'Case';

    public function fullname()
    {
        return "{$this->firstName} {$this->lastName}";
    }

    public function realtimeFullname()
    {
        return $this->fullname();
    }
}

$cache = new \Anteris\Cache\CacheRepository(__DIR__);
$proxy = new \Anteris\Cache\CacheProxy($cache, new MyClass, [
    'realtimeFullname'
]);

// Outputs "Test Case"
echo $proxy->fullname();

// Outputs "Jim Bob"
$proxy->firstName = 'Jim';
$proxy->lastName  = 'Bob';

echo "{$proxy->firstName} {$proxy->lastName}";

// Outputs "Jim Bob"
echo $proxy->realtimeFullname();

// Outputs "Test Case"
echo $proxy->fullname();

```
