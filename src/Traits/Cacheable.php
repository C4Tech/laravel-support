<?php namespace C4tech\Foundation\Traits;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use C4tech\Foundation\Model;

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
     * The Model to wrap in an instance.
     * @var \C4tech\Foundation\Model
     */
    protected $object = null;

    /**
     * Find
     *
     * A glorified Singleton handler that brings along an actual Model.
     * @param  integer $object_id The primary id of the object.
     * @param  boolean $force     Force reloading the data?
     * @return static             Repository wrapper.
     */
    public static function &find($object_id, $force = false)
    {
        $key = static::formatTag($object_id);

        if (!isset(static::$instances[$key]) || $force) {
            $model = static::$class;
            $query = $model::query()
                ->cacheTags($key)
                ->remember(static::$cache_time);

            if ($object = $query->find($object_id)) {
                // Save the instance in memory if we find it
                static::$instances[$key] = static::make($object);
            } elseif ($force) {
                // Remove the in-memory cache if it no longer exists in the DB
                unset(static::$instances[$key]);
            }
        }

        return static::$instances[$key];
    }

    /**
     * Make
     *
     * Create a new, empty model.
     * @return static
     */
    public static function make(Model $model = null)
    {
        return new static($model);
    }

    /**
     * Boot
     *
     * Simple boot method that adds Model event listeners for expiring related cache objects selectively.
     * @return void
     */
    public static function boot($class = null)
    {
        $class = ($class) ?: get_called_class();
        // Flush caches related to the campaign
        if (Config::get('app.debug')) {
            Log::info('Binding cache flusher for model.', ['model' => $class]);
        }
        $clear_cache = function ($model) use ($class) {
            $tags = $class::formatTag($model->id);
            if (Config::get('app.debug')) {
                Log::debug('Flushing model cache', ['tags' => $tags]);
            }
            Cache::tags($tags)->flush();
        };
        static::saved($clear_cache);
        static::deleted($clear_cache);
    }

    /**
     * Set Model
     *
     * Adds the Model to the memory cache
     * @param \C4tech\Foundation\Model $object The model to wrap
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

    /**
     * Build Tag
     *
     * Helper method to create a cache tag for the related model.
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
     * @param  int    $oid Object ID
     * @return string      Cache tag
     */
    public static function formatTag($oid, $suffix = null)
    {
        return static::buildTag(static::$class, $oid, $suffix);
    }

    /**
     * Get Tags
     *
     * Retrieves the tags which should be set for a read query.
     * @return array Cache tag
     */
    public function getTags($suffix = null)
    {
        return [static::formatTag($this->object->id, $suffix)];
    }
}
