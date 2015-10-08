<?php namespace C4tech\Support\Contracts;

interface CachingInterface
{
    /**
     * Get Cache Key
     *
     * Central method to generate an unique key for identifying
     * queries in the cache.
     * @param  string  $suffix    Short text identifier
     * @param  integer $object_id The model's ID
     * @return string
     */
    public function getCacheKey($suffix, $object_id = null);

    /**
     * Get Tags
     *
     * Retrieves the tags which should be set for a read query.
     * @param  string $suffix Additional text to inject into tag
     * @return array  Cache tags
     */
    public function getTags($suffix = null);

    /**
     * Flush Tags
     *
     * Conveniently flush tags from the cache.
     * @param  array|string $tags Suffix to pass through getTags or array of tags.
     * @return void
     */
    public function flushTags($tags);

    /**
     * Cache
     *
     * Conveniently cache results with tags.
     * @param  string       $suffix   Suffix to pass through getCacheId
     * @param  array|string $tags     Suffix to pass through getTags or array of tags
     * @param  integer      $expires  Time (in minutes) to keep cached
     * @param  Closure      $closure  Closure to produce the results
     * @return mixed                  Closure response
     */
    public function cache($suffix, $tags, $closure);
}
