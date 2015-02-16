<?php namespace C4tech\Support\Traits;

use Illuminate\Support\Facades\Config;

/**
 * Presentable
 *
 * A basic trait for models.
 */
trait Presentable
{
    /**
     * Namespaced class of the presenter to load
     * @var string
     */
    protected static $presenter = null;

    /**
     * Get Presenter
     *
     * Default method to return the related presenter (if any)
     * @return mixed
     */
    public function getPresenter()
    {
        $class = Config::get(static::$presenter, 'C4tech\Support\Presenter');
        return new $class($this);
    }
}
