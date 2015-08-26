<?php namespace C4tech\Support\Test;

use C4tech\Support\Test\Traits\Modelable;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
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
        $this->setPropertyValue($this->repo, 'object', $this->mocked_model);
    }

    public function stubCreate($data = null, $return = true)
    {
        $model_instance = Mockery::mock('C4tech\Support\Model');

        Log::shouldReceive('debug')
            ->with(Mockery::type('string'), Mockery::type('array'))
            ->once();

        $model = Mockery::mock('C4tech\Support\Model[create]')
            ->shouldReceive('create')
            ->with($data)
            ->once()
            ->andReturn($model_instance)
            ->getMock();

        $this->repo->shouldReceive('getModelClass')
            ->withNoArgs()
            ->once()
            ->andReturn($model);

        $this->repo->shouldReceive('make')
            ->with($model_instance)
            ->once()
            ->andReturn($return);
    }

    public function stubUpdate($data = null, $return = true)
    {
        Log::shouldReceive('debug')
            ->with(Mockery::type('string'), Mockery::type('array'))
            ->once();

        $this->repo->shouldReceive('getModelClass')
            ->withNoArgs()
            ->once()
            ->andReturn('class');

        $this->mocked_model->shouldReceive('update')
            ->with($data)
            ->once()
            ->andReturn($return);
    }
}
