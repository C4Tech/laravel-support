<?php namespace C4tech\Support;

use C4tech\Support\Contracts\CachingInterface;
use C4tech\Support\Contracts\ModelInterface;
use C4tech\Support\Contracts\ResourceInterface;
use C4tech\Support\Traits\Caching;
use C4tech\Support\Traits\Resource;
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
abstract class Repository implements Arrayable, CachingInterface, Jsonable, JsonSerializable, ResourceInterface
{
    const CACHE_SHORT =     1;
    const CACHE_LONG  =    10;
    const CACHE_HOUR  =    60;
    const CACHE_DAY   =  1440;
    const CACHE_WEEK  = 10080;
    const CACHE_MONTH = 43200;

    use Caching, Resource;

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
