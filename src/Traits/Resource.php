<?php namespace C4tech\Support;

use C4tech\Support\Contracts\ModelInterface;
use C4tech\Support\Exceptions\ModelMismatchException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

trait Resource
{
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
}
