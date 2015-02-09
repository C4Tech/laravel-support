<?php namespace C4tech\Support\Test\Traits;

use Mockery;
use Codeception\Verify;
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
        if (is_object($model)) {
            if ($model instanceof Model) {
                $this->model = $model;
            } else {
                expect($model instanceof Model)->true();
            }
        } elseif (is_string($model)) {
            if (class_exists($model)) {
                $this->model = new $model;
            } else {
                expect(class_exists($model))->true();
            }
        } else {
            expect(false, 'is a Model')->true();
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
