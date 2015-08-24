<?php namespace C4tech\Test\Support;

use C4tech\Support\Controller;
use Codeception\Verify;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\Response;
use Mockery;
use PHPUnit_Framework_TestCase as TestCase;

class ControllerTest extends TestCase
{
    protected $controller;

    protected $data = [
        'success' => false,
        'errors' => []
    ];

    public function setUp()
    {
        $this->controller = Mockery::mock('C4tech\Support\Controller')
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();
    }

    public function tearDown()
    {
        Mockery::close();
        Response::clearResolvedInstances();
    }

    public function testRespondDoesDefaults()
    {
        // defaults
        $status = 200;
        $headers = [];

        Response::shouldReceive('json')
            ->with($this->data, $status, $headers)
            ->once()
            ->andReturn(true);

        expect($this->controller->respond())->true();
    }

    public function testRespondDoesParameters()
    {
        // parameters
        $status = 100;
        $headers = ['coolness' => '(awyeah)'];

        Response::shouldReceive('json')
            ->with($this->data, $status, $headers)
            ->once()
            ->andReturn(true);

        expect($this->controller->respond($status, $headers))->true();
    }

    public function testSuccessCallsResponse()
    {
        $this->controller->shouldReceive('respond')
            ->with(200, [])
            ->once()
            ->andReturn(true);

        expect($this->controller->success())->true();
    }

    public function testFailureCallsResponse()
    {
        $this->controller->shouldReceive('respond')
            ->with(500, [])
            ->once()
            ->andReturn(false);

        expect($this->controller->failure())->false();
    }
}
