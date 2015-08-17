<?php namespace C4tech\Test\Support\Traits;

use Codeception\Verify;
use Illuminate\Support\Facades\Config;
use Mockery;
use PHPUnit_Framework_TestCase as TestCase;
use ReflectionClass;
use stdClass;

class JsonableApiRequestTest extends TestCase
{
    public function tearDown()
    {
        Mockery::close();
        Config::clearResolvedInstances();
    }

    public function testTransformJson()
    {
        $request = new MockRequest;

        Config::shouldReceive('get')
            ->with('c4tech.snakify_json_input', true)
            ->once()
            ->andReturn(false);

        expect($request->transformJson()->all())->equals([]);
    }

    public function testTransformJsonSnake()
    {
        $key = 'key';
        $default = 'default';

        $request = Mockery::mock('C4tech\Test\Support\Traits\MockRequest[snakeJson]');
        $request->shouldReceive('snakeJson')
            ->with($key, $default)
            ->once()
            ->andReturn(true);

        Config::shouldReceive('get')
            ->with('c4tech.snakify_json_input', true)
            ->once()
            ->andReturn(true);

        expect($request->transformJson($key, $default))->true();
    }

    public function testSnakeJson()
    {
        $data = ['test' => true];
        $json = json_encode($data);

        $request = Mockery::mock('C4tech\Test\Support\Traits\MockRequest[convertToSnakeCase, getContent]')
            ->shouldAllowMockingProtectedMethods();
        $request->shouldReceive('convertToSnakeCase')
            ->with($data)
            ->once()
            ->andReturn($data);
        $request->shouldReceive('getContent')
            ->withNoArgs()
            ->once()
            ->andReturn($json);

        $parameters = $request->snakeJson();
        expect($parameters->all())->equals($data);
    }

    public function testSnakeJsonParameters()
    {
        $data = ['test' => true];
        $json = json_encode($data);

        $request = Mockery::mock('C4tech\Test\Support\Traits\MockRequest[convertToSnakeCase, getContent]')
            ->shouldAllowMockingProtectedMethods();
        $request->shouldReceive('convertToSnakeCase')
            ->with($data)
            ->once()
            ->andReturn($data);
        $request->shouldReceive('getContent')
            ->withNoArgs()
            ->once()
            ->andReturn($json);

        expect($request->snakeJson('test'))->true();
        expect($request->snakeJson('extra', false))->false();
    }

    public function testConvertToSnakeCase()
    {
        $mock = new MockRequest;
        $source = [
            'alreadyCamel' => true,
            'nested_array' => [
                'nested_value' => 23,
                'alreadyValue' => 42
            ],
            'uncamel_text' => 'test',
            'alreadyNested' => [
                'nested_string' => 'awesome',
                'alreadyString' => 'nothing'
            ]
        ];

        $target = [
            'already_camel' => true,
            'nested_array' => [
                'nested_value' => 23,
                'already_value' => 42
            ],
            'uncamel_text' => 'test',
            'already_nested' => [
                'nested_string' => 'awesome',
                'already_string' => 'nothing'
            ]
        ];

        $reflection = new ReflectionClass($mock);
        $method = $reflection->getMethod('convertToSnakeCase');
        $method->setAccessible(true);

        expect($method->invoke($mock, $source))->equals($target);
    }
}
