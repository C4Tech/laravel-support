<?php namespace C4tech\Support\Contracts;

interface CachingInterface
{
    /**
     * Get Cache Id
     *
     * Central method to generate an MD5 hash for identifying
     * queries in the cache.
     * @param  string  $suffix    Short text identifier
     * @param  integer $object_id The model's ID
     * @return string
     */
    public function getCacheId($suffix, $object_id = null);

    /**
     * Get Tags
     *
     * Retrieves the tags which should be set for a read query.
     * @param  string $suffix Additional text to inject into tag
     * @return array  Cache tags
     */
    public function getTags($suffix = null);

    /**
     * Format Tag
     *
     * Helper method to create a cache tag for the related model.
     * @param  int    $oid    Object ID
     * @param  string $suffix Additional text to inject into tag
     * @return string         Cache tag
     */
    public function formatTag($oid, $suffix = null);

    public function flushTags($tags);

    public function cache($cache_id, $tags, $closure);
}
