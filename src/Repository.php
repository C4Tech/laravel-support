<?php namespace C4tech\Support;

use C4tech\Support\Contracts\ModelInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use JsonSerializable;

/**
 * Repository
 *
 * Common business logic wrapper to an Eloquent Model.
 */
abstract class Repository implements Arrayable, Jsonable, JsonSerializable
{
    const CACHE_SHORT =     1;
    const CACHE_LONG  =    10;
    const CACHE_HOUR  =    60;
    const CACHE_DAY   =  1440;
    const CACHE_WEEK  = 10080;
    const CACHE_MONTH = 43200;

    /**
     * Holds all instances of the class identified by ID.
     * @var array
     */
    protected static $instances = [];

    /**
     * The namespaced class of the Model to wrap.
     * @var string
     */
    protected static $model = null;

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
     * @return void
     */
    public function boot()
    {
        if (!($model = Config::get(static::$model, static::$model))) {
            return;
        }

        // Flush caches related to the object
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
     * Find or Fail
     *
     * Wrapper to the find method which throws an error if the Model is not found.
     * @param  integer $object_id The primary id of the object.
     * @return static             Repository wrapper.
     * @throws ModelNotFoundException
     */
    public function findOrFail($object_id)
    {
        if ($object = $this->find($object_id)) {
            return $object;
        }

        throw (new ModelNotFoundException)->setModel(static::$model);
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
            $model = Config::get(static::$model, static::$model);
            $query = $model::query()
                ->cacheTags([$key, $cache_key])
                ->remember(self::CACHE_LONG);

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
     * Constructor
     * @param C4tech\Support\Contracts\ModelInterface $object The Model to wrap
     */
    public function __construct(ModelInterface $model = null)
    {
        $this->object = $this->pullModel($model);
        $this->pushCache();
    }

    /**
     * Pull Model
     *
     * Ensure that the underlying object is a Model.
     * @param  C4tech\Support\Contracts\ModelInterface $model Model provided to constructor.
     * @return Model
     */
    protected function pullModel(ModelInterface &$model = null)
    {
        $class = Config::get(static::$model, static::$model);
        return (!is_null($model)) ? $model : new $class;
    }

    /**
     * Push Cache
     *
     * Ensure the instance is stored in memory
     * @return void
     */
    protected function pushCache()
    {
        $key = $this->formatTag($this->object->id);
        if ($this->object->exists && !isset(static::$instances[$key])) {
            static::$instances[$key] = $this;
        }
    }

    /**
     * Make
     *
     * Create a new instance with a live Model.
     * @param  \C4tech\Support\Contracts\ModelInterface $object The Model to wrap
     * @return static
     */
    public function make(ModelInterface $model)
    {
        return new static($model);
    }

    /**
     * Create
     *
     * Creates a new model, fills it with data, and saves it.
     * @return static
     */
    public function create($data = [])
    {
        $model = Config::get(static::$model, static::$model);
        Log::debug('Creating new Model', ['model' => $model, 'data' => $data]);
        $instance = $model::create($data);
        return $this->make($instance);
    }

    /**
     * Update
     *
     * Update the model and saves it.
     * @return static
     */
    public function update($data = [])
    {
        Log::debug(
            'Updating Model',
            [
                'model' => Config::get(static::$model, static::$model),
                'id'    => $this->object->id,
                'data'  => $data
            ]
        );
        return $this->object->update($data);
    }

    /**
     * Delete
     *
     * Delete the model.
     * @return static
     */
    public function delete()
    {
        Log::debug(
            'Deleting Model',
            [
                'model' => Config::get(static::$model, static::$model),
                'id'    => $this->object->id
            ]
        );

        $key = $this->formatTag($this->object->id);
        $status = $this->object->delete();
        unset(static::$instances[$key]);

        return $status;
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
        return static::buildTag(Config::get(static::$model, static::$model), $oid, $suffix);
    }

    /**
     * Build Tag
     *
     * Helper method to create a cache tag for the related model. General format
     * is {model}-{id}(-{suffx})? (e.g. App\Models\Users-18, App\Models\Users-10-posts)
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
     * Convert the model instance to JSON.
     *
     * @param  int  $options
     * @return string
     */
    public function toJson($options = 0)
    {
        return $this->object->toJson($options);
    }

    /**
     * Convert the object into something JSON serializable.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return $this->object->jsonSerialize();
    }

    /**
     * Convert the model instance to an array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->object->toArray();
    }

    /**
     * Get any unknown varible calls from the model.
     *
     * @param  string $var
     * @return mixed
     */
    public function __get($var)
    {
        $getter = 'get' . ucfirst($var);
        return (method_exists($this, $getter)) ? $this->$getter() : $this->object->$var;
    }

    /**
     * Set any unknown varibles to the model.
     *
     * @param  string $var
     * @return mixed
     */
    public function __set($var, $val)
    {
        $setter = 'set' . ucfirst($var);

        if (method_exists($this, $setter)) {
            $this->$setter($val);
        } else {
            $this->object->$var = $val;
        }
    }
}
