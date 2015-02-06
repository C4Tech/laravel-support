<?php namespace C4tech\Support\Traits;

use C4tech\Support\Presenter;

/**
 * Presentable
 *
 * A basic trait for models.
 */
trait Presentable
{
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
