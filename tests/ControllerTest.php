<?php namespace C4tech\Test\Support;

use C4tech\Support\Controller;
use Codeception\Verify;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\View;
use Mockery;
use PHPUnit_Framework_TestCase as TestCase;
use ReflectionClass;

class ControllerTest extends TestCase
{
    protected $controller;

    public function setUp()
    {
        $this->controller = Mockery::mock('C4tech\Support\Controller')
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();
    }

    public function tearDown()
    {
        Mockery::close();
        Log::clearResolvedInstances();
        Request::clearResolvedInstances();
        View::clearResolvedInstances();
        $app = null;
        Facade::setFacadeApplication($app);
    }

    public function testRespondDoesHtmlDefaults()
    {
        // parameters
        $view = '';
        $status = 200;
        $headers = [];

        // return
        $response = 'something';

        Request::shouldReceive('format')
            ->with('json')
            ->once()
            ->andReturn('html');

        Log::shouldReceive('debug')
            ->with('Responding via html', ['view' => $view, 'status' => $status])
            ->once();

        $this->controller->shouldReceive('respondHtml')
            ->with($view, $status, $headers)
            ->once()
            ->andReturn($response);

        expect($this->controller->respond())->equals($response);
    }

    public function testRespondDoesHtmlParameters()
    {
        // parameters
        $view = 'view';
        $status = 100;
        $headers = ['coolness'];

        // return
        $response = 'something';

        Request::shouldReceive('format')
            ->with('json')
            ->once()
            ->andReturn('html');

        Log::shouldReceive('debug')
            ->with('Responding via html', ['view' => $view, 'status' => $status])
            ->once();

        $this->controller->shouldReceive('respondHtml')
            ->with($view, $status, $headers)
            ->once()
            ->andReturn($response);

        expect($this->controller->respond($view, $status, $headers))->equals($response);
    }

    public function testRespondDoesJson()
    {
        // parameters
        $view = '';
        $status = 200;
        $headers = [];

        // return
        $response = 'something';

        Request::shouldReceive('format')
            ->with('json')
            ->once()
            ->andReturn('json');

        Log::shouldReceive('debug')
            ->with('Responding via json', ['view' => $view, 'status' => $status])
            ->once();

        $this->controller->shouldReceive('respondJson')
            ->with($status, $headers)
            ->once()
            ->andReturn($response);

        expect($this->controller->respond())->equals($response);
    }

    public function testRespondDoesJsonDespiteFormat()
    {
        // parameters
        $view = 'json';
        $status = 200;
        $headers = [];

        // return
        $response = 'something';

        Request::shouldReceive('format')
            ->with('json')
            ->once()
            ->andReturn('html');

        Log::shouldReceive('debug')
            ->with('Responding via json', ['view' => $view, 'status' => $status])
            ->once();

        $this->controller->shouldReceive('respondJson')
            ->with($status, $headers)
            ->once()
            ->andReturn($response);

        expect($this->controller->respond($view))->equals($response);
    }

    public function testRespondHtmlDoesDefaults()
    {
        $response = $this->controller->respondHtml();
        $this->verifyIsHtmlResponse($response);
    }

    public function testRespondHtmlDoesParameters()
    {
        // parameters
        $view = 'test';
        $status = 100;
        $headers = ['coolness' => '(awyeah)'];
        $content = 'something';

        $app = [];
        $app['view'] = View::shouldReceive('make')
            ->with($view, Mockery::type('array'))
            ->once()
            ->andReturn($content)
            ->getMock();
        Facade::setFacadeApplication($app);

        $response = $this->controller->respondHtml($view, $status, $headers);
        $this->verifyIsHtmlResponse($response, $status);
        expect($response->headers->all())->hasKey('coolness');
        expect($response->getContent())->equals($content);
    }

    public function testRespondJsonDoesDefaults()
    {
        $response = $this->controller->respondJson();
        $this->verifyIsJsonResponse($response);
        expect($response->getData()->success)->false();
        expect($response->getData()->errors)->isEmpty();
    }

    public function testRespondJsonGetsJsonData()
    {
        $reflection = new ReflectionClass($this->controller);
        $property = $reflection->getProperty('data');
        $property->setAccessible(true);
        $data = $property->getValue($this->controller);
        $data['json']['test'] = true;
        $property->setValue($this->controller, $data);

        $response = $this->controller->respondJson();
        $this->verifyIsJsonResponse($response);
        expect($response->getData()->test)->true();
    }

    public function testRespondJsonDoesParameters()
    {
        // parameters
        $status = 100;
        $headers = ['coolness' => '(awyeah)'];

        $response = $this->controller->respondJson($status, $headers);
        $this->verifyIsJsonResponse($response, $status);
        expect($response->headers->all())->hasKey('coolness');
        expect($response->getData()->success)->false();
        expect($response->getData()->errors)->isEmpty();
    }

    protected function verifyIsHtmlResponse(&$response, $status = 200)
    {
        expect(get_class($response))->equals('Illuminate\Http\Response');
        expect(get_class($response->headers))->equals('Symfony\Component\HttpFoundation\ResponseHeaderBag');
        expect($response->getStatusCode())->equals($status);
    }

    protected function verifyIsJsonResponse(&$response, $status = 200)
    {
        expect(get_class($response))->equals('Illuminate\Http\JsonResponse');
        expect(get_class($response->headers))->equals('Symfony\Component\HttpFoundation\ResponseHeaderBag');
        expect($response->getStatusCode())->equals($status);
    }

    public function testSetupLayoutNull()
    {
        expect_not($this->controller->setupLayout());

        $reflection = new ReflectionClass($this->controller);
        $property = $reflection->getProperty('layout');
        $property->setAccessible(true);
        $value = $property->getValue($this->controller);

        expect($value)->null();
    }

    public function testSetupLayoutSet()
    {
        $layout = 'test';
        $view = '(awyeah)';

        View::shouldReceive('make')
            ->with($layout)
            ->andReturn($view);

        $reflection = new ReflectionClass($this->controller);
        $property = $reflection->getProperty('layout');
        $property->setAccessible(true);
        $property->setValue($this->controller, $layout);

        expect_not($this->controller->setupLayout());

        $reflection = new ReflectionClass($this->controller);
        $reflection->getProperty('layout');
        $property->setAccessible(true);
        $value = $property->getValue($this->controller);

        expect($value)->equals($view);
    }
}
