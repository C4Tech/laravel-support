<?php namespace C4tech\Support;

use Robbo\Presenter\Presenter as BasePresenter;

/**
 * A foundation Presenter with useful features.
 */
class Presenter extends BasePresenter
{
    /**
     * Namespaced name of the repository to load
     * @var string
     */
    protected $repository_class = null;

    /**
     * Model Repository
     * @var C4tech\Foundation\Repository
     */
    protected $repository_instance = null;

    /**
     * Present Repository
     *
     * Allows calling $this->repository->method() and autoloading the
     * repository as necessary.
     * @return C4tech\Foundation\Repository A Repository if it exists
     */
    public function presentRepository()
    {
        if (is_null($this->repository_instance)) {
            $this->repository_instance = $this->setRepository();
        }

        return $this->repository_instance;
    }

    /**
     * Set Repository
     *
     * An overloadable method to construct the repository.
     * @return C4tech\Foundation\Repository A Repository if it is defined.
     */
    protected function setRepository()
    {
        if ($class = $this->repository_class) {
            return new $class($this->object);
        }
    }
}
