<?php namespace C4tech\Test\Support;

use Codeception\Verify;
use Illuminate\Support\Facades\Config;
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

    public function tearDown()
    {
        Mockery::close();
        Config::clearResolvedInstances();
    }

    public function testGetDates()
    {
        $dates = $this->model->getDates();

        expect($dates)->contains('created_at');
        expect($dates)->contains('updated_at');
        expect($dates)->contains('deleted_at');
    }

    public function testToArray()
    {
        $this->model->test_thing = 123;
        Config::shouldReceive('get')
            ->with('c4tech.jsonify_output', true)
            ->once()
            ->andReturn(false);

        expect($this->model->toArray())->equals(['test_thing' => 123]);
    }

    public function testToArrayJsonify()
    {
        $this->model->test_thing = 123;
        $this->model->shouldReceive('convertToCamelCase')
            ->with(['test_thing' => 123])
            ->once()
            ->andReturn(false);

        Config::shouldReceive('get')
            ->with('c4tech.jsonify_output', true)
            ->once()
            ->andReturn(true);

        expect($this->model->toArray())->false();
    }
}
