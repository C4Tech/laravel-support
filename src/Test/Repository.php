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

    public function stubCreate($config_model = null, $data = null, $return = true)
    {
        $model_instance = Mockery::mock('C4tech\Support\Model');

        $model = Mockery::mock('C4tech\Support\Model[create]')
            ->shouldReceive('create')
            ->with($data)
            ->once()
            ->andReturn($model_instance)
            ->getMock();

        Config::shouldReceive('get')
            ->with($config_model, $config_model)
            ->once()
            ->andReturn($model);

        Log::shouldReceive('debug')
            ->with(Mockery::type('string'), Mockery::type('array'))
            ->once();

        $this->repo->shouldReceive('make')
            ->with($model_instance)
            ->once()
            ->andReturn($return);
    }
}
