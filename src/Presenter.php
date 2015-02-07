<?php namespace C4tech\Support;

use Robbo\Presenter\Presenter as BasePresenter;

/**
 * A foundation Presenter with useful features.
 */
class Presenter extends BasePresenter
{
    /**
     * Namespaced class of the repository to load
     * @var string
     */
    protected static $repository = null;

    /**
     * Model Repository
     * @var C4tech\Foundation\Repository
     */
    protected $instance = null;

    /**
     * Present Repository
     *
     * Allows calling $this->repository->method() and autoloading the
     * repository as necessary.
     * @return C4tech\Foundation\Repository A Repository if it exists
     */
    public function presentRepo()
    {
        if (is_null($this->instance)) {
            $this->instance = $this->setRepository();
        }

        return $this->instance;
    }

    /**
     * Set Repository
     *
     * An overridable method to construct the repository.
     * @return C4tech\Support\Repository
     */
    protected function setRepository()
    {
        if ($class = static::$repository) {
            return new $class($this->object);
        }
    }
}
