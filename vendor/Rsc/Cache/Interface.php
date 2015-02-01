<?php

/**
 * Interface Rsc_Cache_Interface
 * Interface for caching data within the framework
 *
 * @package Rsc\Cache
 * @author Artur Kovalevsky <kovalevskyproj@gmail.com>
 * @copyright Copyright (c) 2015, supsystic.com
 * @link supsystic.com
 */
interface Rsc_Cache_Interface
{
    /**
     * Caches data
     * @param string $key The key
     * @param mixed $data Data for caching
     * @param int $ttl Lifetime of the cached data
     * @return bool TRUE if the data is successfully written to the cache, FALSE otherwise
     */
    public function set($key, $data, $ttl = 3600);

    /**
     * Returns data from the cache if it is fresh
     * @param string $key The key
     * @return mixed|null Cached data or NULL if the lifetime of the cache has expired or data not found
     */
    public function get($key);

    /**
     * Remove cached data
     * @param string $key The key
     * @return bool TRUE on success, FALSE otherwise
     */
    public function delete($key);

    /**
     * Clear the cache
     * @return bool TRUE on success, FALSE otherwise
     */
    public function clear();
} 