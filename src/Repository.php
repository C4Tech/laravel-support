<?php namespace C4tech\Support;

use C4tech\Support\Contracts\ModelInterface;
use C4tech\Support\Contracts\ResourceInterface;
use C4tech\Support\Exceptions\ModelMismatchException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use JsonSerializable;

/**
 * Repository
 *
 * Common business logic wrapper to an Eloquent Model.
 */
abstract class Repository implements Arrayable, Jsonable, JsonSerializable, ResourceInterface
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
     * @var \C4tech\Support\Model
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
        if (!($model = $this->getModelClass())) {
            return;
        }

        // Flush caches related to the object
        if (Config::get('app.debug')) {
            Log::debug('Binding cache flusher for model.', ['model' => $model]);
        }

        $clear_cache = function ($object) {
            $repository = $this->make($object);
            $tags = $repository->getTags('object');
            if (Config::get('app.debug')) {
                Log::debug('Flushing model cache', ['tags' => $tags]);
            }
            Cache::tags($tags)->flush();
        };

        $model::saved($clear_cache);
        $model::deleted($clear_cache);
    }

    /**
     * @inheritDoc
     */
    public function findOrFail($object_id)
    {
        if ($object = $this->find($object_id)) {
            return $object;
        }

        throw (new ModelNotFoundException)->setModel(static::$model);
    }

    /**
     * @inheritDoc
     */
    public function &find($object_id, $force = false)
    {
        $key = $this->formatTag($object_id);
        $cache_key = $this->formatTag($object_id, 'object');

        if (!isset(static::$instances[$key]) || $force) {
            $model = $this->getModelClass();

            // Remove the in-memory cache
            if ($force) {
                unset(static::$instances[$key]);
            }

            $object = Cache::tags([$key, $cache_key])
                ->remember(
                    $this->getCacheId('object', $object_id),
                    self::CACHE_LONG,
                    function () use ($model, $object_id) {
                        return $model::find($object_id);
                    }
                );

            // Save the instance in memory if we find it
            if ($object) {
                static::$instances[$key] = $this->make($object);
            }
        }

        return static::$instances[$key];
    }

    /**
     * Constructor
     * @param C4tech\Support\Contracts\ModelInterface $model The Model to wrap
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
        $class = $this->getModelClass();
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
     * @inheritDoc
     */
    public function make(ModelInterface $model)
    {
        $class = $this->getModelClass();

        if (!($model instanceof $class)) {
            throw new ModelMismatchException();
        }

        return new static($model);
    }

    /**
     * @inheritDoc
     */
    public function makeCollection($models)
    {
        if (!($models instanceof Collection)) {
            $models = new Collection([$models]);
        }

        return $models->map(function ($model) {
            return $this->make($model);
        });
    }

    /**
     * @inheritDoc
     */
    public function getModelClass()
    {
        return Config::get(static::$model, static::$model);
    }

    /**
     * @inheritDoc
     */
    public function getCacheId($suffix, $object_id = null)
    {
        if (!isset($object_id)) {
            $object_id = '';

            if ($this->object && $this->object->id) {
                $object_id = $this->object->id;
            }
        }

        return md5($this->getModelClass() . $object_id . $suffix);
    }

    /**
     * @inheritDoc
     */
    public function create(array $data = [])
    {
        $model = $this->getModelClass();
        Log::info('Creating new Model', ['model' => $model, 'data' => $data]);
        $instance = $model::create($data);
        return $this->make($instance);
    }

    /**
     * @inheritDoc
     */
    public function update(array $data = [])
    {
        Log::info(
            'Updating Model',
            [
                'model' => $this->getModelClass(),
                'id'    => $this->object->id
            ]
        );
        return $this->object->update($data);
    }

    /**
     * @inheritDoc
     */
    public function delete()
    {
        Log::info(
            'Deleting Model',
            [
                'model' => $this->getModelClass(),
                'id'    => $this->object->id
            ]
        );

        $key = $this->formatTag($this->object->id);
        $status = $this->object->delete();
        unset(static::$instances[$key]);

        return $status;
    }

    /**
     * @inheritDoc
     */
    public function getAll()
    {
        $model = $this->getModelClass();
        return Cache::tags([$model])
            ->remember(
                $this->getCacheId('all', null),
                self::CACHE_LONG,
                function () use ($model) {
                    $results = $model::all();
                    return ($results->count()) ? $this->makeCollection($results) : $results;
                }
            );
    }

    /**
     * @inheritDoc
     */
    public function &getModel()
    {
        return $this->object;
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
     * @inheritDoc
     */
    public function formatTag($oid, $suffix = null)
    {
        return static::buildTag($this->getModelClass(), $oid, $suffix);
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
     * @inheritDoc
     */
    public function toJson($options = 0)
    {
        return json_encode($this->jsonSerialize(), $options);
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    /**
     * @inheritDoc
     */
    public function toArray()
    {
        return $this->object->toArray();
    }

    /**
     * @inheritDoc
     */
    public function __toString()
    {
        return $this->toJson();
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
