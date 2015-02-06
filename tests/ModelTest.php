<?php namespace C4tech\Test\Support;

use Codeception\Verify;
use Mockery;
use PHPUnit_Framework_TestCase as TestCase;

class ModelTest extends TestCase
{
    protected $model;

    public function setUp()
    {
        $this->model = Mockery::mock('C4tech\Support\Model')
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();
    }

    public function testGetDates()
    {
        $dates = $this->model->getDates();

        expect($dates)->contains('created_at');
        expect($dates)->contains('updated_at');
        expect($dates)->contains('deleted_at');
    }
}
