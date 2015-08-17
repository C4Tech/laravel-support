<?php namespace C4tech\Test\Support;

use Codeception\Verify;
use Mockery;
use PHPUnit_Framework_TestCase as TestCase;

class RequestTest extends TestCase
{
    public function testJson()
    {
        $request = Mockery::mock('C4tech\Support\Request[transformJson]')
            ->shouldAllowMockingProtectedMethods();

        $request->shouldReceive('transformJson')
            ->with(null, null)
            ->once()
            ->andReturn(false);

        expect($request->json())->false();

        Mockery::close();
    }

    public function testJsonParameters()
    {
        $key = 'pass';
        $default = 13;

        $request = Mockery::mock('C4tech\Support\Request[transformJson]')
            ->shouldAllowMockingProtectedMethods();

        $request->shouldReceive('transformJson')
            ->with($key, $default)
            ->once()
            ->andReturn(true);

        expect($request->json($key, $default))->true();

        Mockery::close();
    }
}
