<?php namespace C4tech\Support\Test\Traits;

use Codeception\Verify;

trait Presentable
{
    protected function verifyGetPresenter($class)
    {
        $presenter = $this->model->getPresenter();
        expect(get_class($presenter))->equals($class);
        expect($presenter->getObject())->equals($this->model);
    }
}
