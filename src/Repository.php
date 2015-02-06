<?php namespace C4tech\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Robbo\Presenter\PresentableInterface;

/**
 * Repository
 *
 * Common business logic wrapper to an Eloquent Model.
 */
abstract class Repository implements PresentableInterface
{
    /**
     * Holds all instances of the class identified by ID.
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
    public function boot()
    {
        $model = static::$class;

        if (!$model) {
            return;
        }

        // Flush caches related to the campaign
        if (Config::get('app.debug')) {
            Log::info('Binding cache flusher for model.', ['model' => $model]);
        }

        $clear_cache = function ($object) {
            $tag = $this->formatTag($object->id, 'object');
            if (Config::get('app.debug')) {
                Log::debug('Flushing model cache', ['tag' => $tag]);
            }
            Cache::tags([$tag])->flush();
        };

        $model::saved($clear_cache);
        $model::deleted($clear_cache);
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
        $key = $this->formatTag($object_id);
        $cache_key = $this->formatTag($object_id, 'object');

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
                static::$instances[$key] = new static($object);
            }
        }

        return static::$instances[$key];
    }

    /**
     * Constructor
     * @param \C4tech\Support\Model $object The Model to wrap
     */
    public function __construct(Model $model = null)
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
     * Create
     *
     * Creates a new model, fills it with data, and saves it.
     * @return static
     */
    public function create($data = [])
    {
        $model = static::$class;
        Log::debug('Creating new Model', ['model' => $model, 'data' => $data]);
        return new static($model::create($data));
    }

    /**
     * Update
     *
     * Update the model and saves it.
     * @return static
     */
    public function update($data = [])
    {
        Log::debug('Updating Model', ['model' => static::$class, 'id' => $this->object->id, 'data' => $data]);
        return $this->object->update($data);
    }

    /**
     * Get Model
     *
     * Access the underlying model (necessary for methods associating relationships).
     * @return \C4tech\Support\Model
     */
    public function &getModel()
    {
        return $this->object;
    }

    /**
     * Get Presenter
     *
     * Default method to return the model's presenter.
     * @return mixed
     */
    public function getPresenter()
    {
        return $this->object->getPresenter();
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
    public function formatTag($oid, $suffix = null)
    {
        return static::buildTag(static::$class, $oid, $suffix);
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
    protected static function buildTag($prefix, $oid = null, $suffix = null)
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
     * Get any unknown varible calls from the model.
     *
     * @param  string $var
     * @return mixed
     */
    public function __get($var)
    {
        return $this->object->$var;
    }

    /**
     * Set any unknown varibles to the model.
     *
     * @param  string $var
     * @return mixed
     */
    public function __set($var, $val)
    {
        $this->object->$var = $val;
    }
}
