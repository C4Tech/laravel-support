<?php namespace C4tech\Foundation;

use Illuminate\Database\Eloquent\Model as BaseModel;
use Robbo\Presenter\PresentableInterface;
use C4tech\Foundation\Traits\DateFilter;

/**
 * A foundation Model with useful features.
 */
class Model extends BaseModel implements PresentableInterface
{
    /**
     * Consume the DateFilter traits.
     */
    use DateFilter;

    /**
     * @inheritdoc
     */
    protected $guarded = ['id', 'created_at', 'updated_at', 'deleted_at'];

    /**
     * @inheritDoc
     */
    protected $dates = ['deleted_at'];

    /**
     * Get Dates
     *
     * Overloads the defined database fields which ought to be converted t.
     * Carbon objects.
     * @return array Column names to transform.
     */
    public function getDates()
    {
        return array_merge(parent::getDates(), ['deleted_at']);
    }

    /**
     * Get Presenter
     *
     * Default method to return the related presenter (if any)
     * @return mixed
     */
    public function getPresenter()
    {
        return new Presenter($this);
    }
}
