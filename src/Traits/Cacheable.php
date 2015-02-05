<?php namespace C4tech\Support\Traits;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use C4tech\Support\Model;

/**
 * Functionality related to caching data.
 */
trait Cacheable
{
    /**
     * Holds all instances of the repo identified by Model ID.
     * @var array
     */
    protected static $instances = [];

    /**
     * The full, namespaced class name of the Model to wrap.
     * @var string
     */
    protected static $class = null;

    /**
     * Default length of time in minutes to cache a query.
     * @var integer
     */
    protected static $cache_time = 10;

    /**
     * The Model wrapped in the instance.
     * @var \C4tech\Foundation\Model
     */
    protected $object = null;

    /**
     * Boot
     *
     * Simple boot method that adds Model event listeners for expiring related
     * cache objects selectively.
     * @param  string $class Class name to bind
     * @return void
     */
    public static function boot($class = null)
    {
        $class = ($class) ?: get_called_class();
        $model = static::$class;

        if (!$model) {
            return;
        }

        // Flush caches related to the campaign
        if (Config::get('app.debug')) {
            Log::info('Binding cache flusher for model.', ['model' => $model]);
        }

        $clear_cache = function ($object) use ($class) {
            $tag = $class::formatTag($object->id, 'object');
            if (Config::get('app.debug')) {
                Log::debug('Flushing model cache', ['tag' => $tag]);
            }
            Cache::tags([$tag])->flush();
        };

        $model::saved($clear_cache);
        $model::deleted($clear_cache);
    }

    /**
     * Build Tag
     *
     * Helper method to create a cache tag for the related model. General format
     * is {class}-{id}(-{suffx})? (e.g. App\Models\Users-18, App\Models\Users-10-posts)
     * @param  string $base   Base tag
     * @param  int    $oid    Object ID
     * @param  string $suffix Additional text to inject into tag
     * @return string         Cache tag
     */
    public static function buildTag($prefix, $oid = null, $suffix = null)
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
     * Format Tag
     *
     * Helper method to create a cache tag for the related model.
     * @param  int    $oid    Object ID
     * @param  string $suffix Additional text to inject into tag
     * @return string         Cache tag
     */
    public static function formatTag($oid, $suffix = null)
    {
        return static::buildTag(static::$class, $oid, $suffix);
    }

    /**
     * Get Tags
     *
     * Retrieves the tags which should be set for a read query.
     * @param  string $suffix Additional text to inject into tag
     * @return array  Cache tags
     */
    public function getTags($suffix = null)
    {
        $tags = [static::formatTag($this->object->id)];
        if (!is_null($suffix)) {
            $tags[] = static::formatTag($this->object->id, $suffix);
        }

        return $tags;
    }

    /**
     * Find
     *
     * A glorified Manager that brings along an actual Model.
     * @param  integer $object_id The primary id of the object.
     * @param  boolean $force     Force reloading the data?
     * @return static             Repository wrapper.
     */
    public function &find($object_id, $force = false)
    {
        $key = static::formatTag($object_id);
        $cache_key = static::formatTag($object_id, 'object');

        if (!isset(static::$instances[$key]) || $force) {
            $model = static::$class;
            $query = $model::query()
                ->cacheTags([$key, $cache_key])
                ->remember(static::$cache_time);

            // Remove the in-memory cache
            if ($force) {
                unset(static::$instances[$key]);
            }

            // Save the instance in memory if we find it
            if ($object = $query->find($object_id)) {
                static::$instances[$key] = $this->make($object);
            }
        }

        return static::$instances[$key];
    }

    /**
     * Make
     *
     * Create a new, empty model.
     * @param \C4tech\Support\Model $object The model to wrap
     * @return static
     */
    public function make(Model $model = null)
    {
        return new static($model);
    }

    /**
     * Set Model
     *
     * Adds the Model to the memory cache
     * @param \C4tech\Support\Model $object The model to wrap
     */
    protected function setModel(Model $model = null)
    {
        if (is_null($model)) {
            $class = static::$class;
            $model = new $class;
        }

        $this->object = $model;
        $key = static::formatTag($model->id);
        if ($model->exists && !isset(static::$instances[$key])) {
            static::$instances[$key] = $this;
        }
    }
}
