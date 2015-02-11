<?php namespace C4tech\Support\Test;

use C4tech\Support\Test\Traits\Modelable;
use Mockery;

abstract class Repository extends Base
{
    /**
     * Consume the Model testing traits
     */
    use Modelable;

    protected $repo;
    protected $mocked_model;

    public function setRepository($repository, $model)
    {
        $this->setModel($model);
        $this->repo = Mockery::mock($repository)
            ->makePartial();

        $this->mocked_model = $this->getModelMock();
        $property = $this->getProperty($this->repo, 'object');
        $property->setValue($this->repo, $this->mocked_model);
    }
}
