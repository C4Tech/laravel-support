<?php namespace C4tech\Support\Test\Traits;

use Mockery;
use C4tech\Support\Model;

trait Modelable
{
    /**
     * Model for testing
     * @var C4tech\Support\Model
     */
    protected $model;

    /**
     * Set Model
     *
     * Set the internal model for testing
     * @param  string|C4tech\Support\Model $model The Model to test.
     * @return void
     */
    protected function setModel($model)
    {
        if ($model instanceof Model) {
            $this->model = $model;
        } elseif (class_exists($model)) {
            $this->model = new $model;
        } else {
            expect(class_exists($model))->true();
            expect($model instanceof Model)->true();
        }
    }

    /**
     * Get Model Mock
     *
     * Create a new mock for the Model.
     * @return \Mockery\MockInterface
     */
    protected function getModelMock()
    {
        return Mockery::mock(get_class($this->model))->makePartial();
    }
}
