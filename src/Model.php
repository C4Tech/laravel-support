<?php namespace C4tech\Support;

use C4tech\Support\Contracts\ModelInterface;
use C4tech\Support\Traits\DateFilter;
use C4tech\Support\Traits\JsonableApiModel;
use Illuminate\Database\Eloquent\Model as BaseModel;

/**
 * A foundation Model with useful features.
 */
class Model extends BaseModel implements ModelInterface
{
    /**
     * Consume traits.
     */
    use DateFilter, JsonableApiModel;

    /**
     * @inheritdoc
     */
    protected $guarded = ['id', 'created_at', 'updated_at', 'deleted_at'];

    /**
     * Get Dates
     *
     * Overloads the defined database fields which ought to be converted to
     * Carbon objects in order to add deleted_at as a default.
     * @return array Column names to transform.
     */
    public function getDates()
    {
        return array_merge(parent::getDates(), ['deleted_at']);
    }

    /**
     * Overloads the jsonified model data in order to convert
     * snake_case keys into camelCase.
     *
     * @param  int  $options
     * @return string
     */
    public function toJson($options = 0)
    {
        return json_encode($this->toJsonableArray(), $options);
    }

    /**
     * JSON Serialize
     *
     * Overloads the jsonable model data in order to convert
     * snake_case keys into camelCase.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return $this->toJsonableArray();
    }
}
