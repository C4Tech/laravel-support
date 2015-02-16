<?php namespace C4tech\Support\Test\Traits;

use Codeception\Verify;
use Illuminate\Support\Facades\Config;

trait Presentable
{
    protected function verifyGetPresenter($class, $config = null)
    {
        $default_presenter = 'C4tech\Support\Presenter';

        Config::shouldReceive('get')
            ->with($config, $default_presenter)
            ->once()
            ->andReturn($class);

        $presenter = $this->model->getPresenter();
        expect(get_class($presenter))->equals($class);
        expect($presenter->getObject())->equals($this->model);
    }
}
