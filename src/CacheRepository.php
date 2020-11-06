<?php

namespace Anteris\Cache;

use Anteris\Cache\Exceptions\FilePermissionsException;
use DirectoryIterator;
use IteratorIterator;

/**
 * This class is the cache repository. It allows you to read from and write to
 * the cache.
 */
class CacheRepository
{
    /** @var array Keeps track of the MD5 equivalent of the string so we don't have to keep converting it. */
    protected array $cacheKeys = [];

    /** @var string The path to store our cache directory in. */
    protected string $path;

    /** @var string The name of our cache directory. */
    protected string $directory = '.cache';

    /**
     * Sets up the class.
     *
     * @param  string  $path  The path to store our cache directory in.
     */
    public function __construct(?string $path = null)
    {
        $this->path = ($path) ? rtrim($path, '\\/') : getcwd();
    }

    /**
     * Clears all items from the cache.
     */
    public function clear()
    {
        $this->removeDirectoryContents($this->getDirectoryPath(), true);
    }

    /**
     * Returns an item from the cache.
     *
     * @param  string  $key  The cached item identifier.
     * @param  mixed   $default  The value to default to if the item is not found. If a callable, the result of the callable will be cached and returned.
     *
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        $md5    = $this->getMd5($key);
        $path   = $this->getDirectoryPath($md5);

        if (file_exists($path)) {
            return $this->getFileContents($path);
        }

        if (is_callable($default)) {
            $result = $default();
            $this->set($key, $result);

            return $result;
        }

        return $default;
    }

    /**
     * Determines whether or not the key exists in the cache.
     *
     * @param  string  $key  The key to check the existance for.
     */
    public function has(string $key): bool
    {
        return file_exists($this->getDirectoryPath(
            $this->getMd5($key)
        ));
    }

    /**
     * Sets an item in the cache.
     *
     * @param  string  $key  An identifier for the item in the cache.
     * @param  mixed   $content  The content to be cached.
     */
    public function set(string $key, $content): void
    {
        $this->setFileContents(
            $this->getDirectoryPath($this->getMd5($key)),
            $content
        );
    }

    /**
     * Returns the name of the cache directory.
     */
    public function getCacheDirectory(): string
    {
        return $this->directory;
    }

    /**
     * Sets the name of the cache directory.
     */
    public function setCacheDirectory(string $directory)
    {
        $this->directory = trim($directory, '\\/');
    }

    /**
     * Returns the cache directory path.
     *
     * @param  null|string  $appends  Anything to be appended to the path.
     * @return string
     */
    protected function getDirectoryPath(?string $appends = null)
    {
        $path = $this->path . '/' . $this->directory;

        if ($appends) {
            $path .= "/$appends";
        }

        return $path;
    }

    /**
     * Attempts to read the contents of the file.
     *
     * @param  string  $path  The file to be read.
     * @return mixed
     *
     * @throws \Anteris\Cache\Exceptions\FilePermissionsException
     */
    protected function getFileContents(string $path)
    {
        if (! $contents = file_get_contents($path)) {
            // @codeCoverageIgnoreStart
            throw new FilePermissionsException("Unable to read contents of $path!");
            // @codeCoverageIgnoreEnd
        }

        return unserialize($contents);
    }

    /**
     * Returns the MD5 equivalent of a string.
     *
     * @param  string  $key  The string to be converted to an MD5 hash.
     * @return string
     */
    protected function getMd5(string $key): string
    {
        if (! isset($this->cacheKeys[$key])) {
            $this->cacheKeys[$key] = md5($key);
        }

        return $this->cacheKeys[$key];
    }

    /**
     * Removes the contents of a directory.
     *
     * @param  string  $directory  The directory to have its contents removed.
     */
    protected function removeDirectoryContents(string $directory, bool $preserveParent = false): void
    {
        $directoryIterator  = new DirectoryIterator($directory);
        $items              = new IteratorIterator($directoryIterator);
        
        foreach ($items as $item) {
            if ($item->isDot()) {
                continue;
            }

            if ($item->isDir()) {
                $this->removeDirectoryContents($item->getPathName(), false);

                continue;
            }

            unlink($item->getPathName());
        }

        if (! $preserveParent) {
            rmdir($directory);
        }
    }

    /**
     * Attempts to write the contents of the file.
     *
     * @param  string  $path  The file to be written to.
     * @param  mixed   $content  The file contents to write.
     *
     * @throws \Anteris\Cache\Exceptions\FilePermissionsException
     */
    protected function setFileContents(string $path, $content): void
    {
        // First ensure the directory exists
        $directory = $this->getDirectoryPath();

        if (! is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        // Then write the file
        if (! file_put_contents($path, serialize($content))) {
            // @codeCoverageIgnoreStart
            throw new FilePermissionsException("Unable to write to $path!");
            // @codeCoverageIgnoreEnd
        }
    }
}
