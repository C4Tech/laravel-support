<?php namespace C4tech\Foundation;

use Illuminate\Support\Facades\Log;
use Robbo\Presenter\PresentableInterface;
use C4tech\Foundation\Traits\Cacheable;

/**
 * Proxy Repository
 *
 * Common business logic wrapper to an Eloquent Model.
 */
abstract class Repository implements PresentableInterface
{
    /**
     * Consume Cacheable-like properties.
     */
    use Cacheable;

    /**
     * Constructor
     * @param \C4tech\Foundation\Model $object The Model to wrap
     */
    public function __construct(Model $model = null)
    {
        $this->setModel($model);
    }

    /**
     * Create
     *
     * Creates a new model, fills it with data, and saves it.
     * @return static
     */
    public static function create($data = [])
    {
        $model = static::$class;
        Log::debug('Creating new Model', ['model' => $model, 'data' => $data]);
        $new = $model::create($data);
        return new static($new);
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
     * Access the underlying model (sometimes necessary for a method).
     * @return \Illuminate\Datase\Eloquent\Model
     */
    public function &getModel()
    {
        return $this->object;
    }

    /**
     * @inheritDoc
     */
    public function getPresenter()
    {
        return $this->object->getPresenter();
    }

    /**
     * Pass any unknown varible calls to the injected object.
     *
     * @param  string $var
     * @return mixed
     */
    public function __get($var)
    {
        return $this->object->$var;
    }

    /**
     * Set any unknown varibles to the injected object.
     *
     * @param  string $var
     * @return mixed
     */
    public function __set($var, $val)
    {
        $this->object->$var = $val;
    }
}
