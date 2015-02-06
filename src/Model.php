<?php namespace C4tech\Support;

use Illuminate\Database\Eloquent\Model as BaseModel;
use Robbo\Presenter\PresentableInterface;
use C4tech\Support\Traits\DateFilter;
use C4tech\Support\Traits\Presentable;

/**
 * A foundation Model with useful features.
 */
class Model extends BaseModel implements PresentableInterface
{
    /**
     * Consume the Presentable and DateFilter traits.
     */
    use DateFilter, Presentable;

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
}
