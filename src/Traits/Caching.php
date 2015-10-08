<?php namespace C4tech\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

trait Caching
{
    /**
     * Base string for cache keys and tags.
     * @var string
     */
    protected $cache_base = 'model';

    /**
     * @inheritDoc
     */
    public function getCacheKey($suffix, $object_id = null)
    {
        if (!isset($object_id)) {
            $object_id = '';

            if ($this->object && $this->object->id) {
                $object_id = $this->object->id;
            }
        }

        return md5($this->cache_base . $object_id . $suffix);
    }

    /**
     * Get Cache Id
     *
     * Deprecated. Use getCacheKey.
     * @param  string  $suffix    Short text identifier
     * @param  integer $object_id The model's ID
     * @return string
     * @deprecated since 3.2.0
     */
    public function getCacheId($suffix, $object_id = null)
    {
        return $this->getCacheKey($suffix, $object_id);
    }

    /**
     * @inheritDoc
     */
    public function getTags($suffix = null)
    {
        $tags = [$this->formatTag($this->object->id)];
        if (!is_null($suffix)) {
            $tags[] = $this->formatTag($this->object->id, $suffix);
        }

        return $tags;
    }

    /**
     * Format Tag
     *
     * Helper method to create a cache tag for the related model.
     * @param  int    $oid    Object ID
     * @param  string $suffix Additional text to inject into tag
     * @return string         Cache tag
     */
    protected function formatTag($oid, $suffix = null)
    {
        return $this->buildTag($this->cache_base, $oid, $suffix);
    }

    /**
     * Build Tag
     *
     * Helper method to create a cache tag for the related model. General format
     * is {model}-{id}(-{suffx})? (e.g. App\Models\Users-18, App\Models\Users-10-posts)
     * @param  string $prefix Base tag
     * @param  int    $oid    Object ID
     * @param  string $suffix Additional text to inject into tag
     * @return string         Cache tag
     */
    protected function buildTag($prefix, $oid = null, $suffix = null)
    {
        if (is_null($oid)) {
            return str_plural($prefix);
        } elseif (is_null($suffix)) {
            return $prefix . '-' . $oid;
        } else {
            return $prefix . '-' . $oid . '-' . $suffix;
        }
    }

    /**
     * @inheritDoc
     */
    public function flushTags($tags)
    {
        if (!is_array($tags)) {
            $tags = $this->getTags($tags);
        }

        if (Config::get('app.debug')) {
            Log::debug('Flushing model cache', ['tags' => $tags]);
        }

        Cache::tags($tags)->flush();
    }

    /**
     * @inheritDoc
     */
    public function cache($suffix, $tags, $expires, $closure)
    {
        $key = $this->getCacheKey($suffix);
        if (!is_array($tags)) {
            $tags = $this->getTags($tags);
        }

        return Cache::tags($tags)->remember($key, $expires, $closure);
    }
}
