<?php namespace C4tech\Test\Support\Test;

use C4tech\Support\Test\Facade;
use Mockery;
use Mockery\MockInterface;
use PHPUnit_Framework_TestCase as TestCase;
use ReflectionClass;

class RepositoryTest extends TestCase
{
    public function setUp()
    {
        $this->object = Mockery::mock('C4tech\Support\Test\Repository')
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();
    }

    public function tearDown()
    {
        Mockery::close();
    }

    protected function getMethod($method)
    {
        $reflection = new ReflectionClass($this->object);
        $method = $reflection->getMethod($method);
        $method->setAccessible(true);
        return $method;
    }

    protected function getProperty($property)
    {
        $reflection = new ReflectionClass($this->object);
        $property = $reflection->getProperty($property);
        $property->setAccessible(true);
        return $property;
    }

    public function testSetRepository()
    {
        $repo = 'C4tech\Support\Repository';
        $model = 'C4tech\Support\Model';
        $mock = Mockery::mock($model);

        $this->object->shouldReceive('setModel')
            ->with($model)
            ->once();
        $this->object->shouldReceive('getModelMock')
            ->withNoArgs()
            ->once()
            ->andReturn($mock);

        $method = $this->getMethod('setRepository');
        $method->invoke($this->object, $repo, $model);

        // Ensure the mocked_model is set
        $property = $this->getProperty('mocked_model');
        $mocked_model = $property->getValue($this->object);
        expect($mocked_model)->equals($mock);

        // Ensure the repo is set
        $property = $this->getProperty('repo');
        $mocked_repo = $property->getValue($this->object);
        expect($mocked_repo instanceof MockInterface)->true();

        // ensure the repo has the mocked_model
        $reflection = new ReflectionClass($mocked_repo);
        $property = $reflection->getProperty('object');
        $property->setAccessible(true);
        $repo_model = $property->getValue($mocked_repo);
        expect($repo_model)->equals($mocked_model);
    }

    public function testStubCreate()
    {
        $this->object->setRepository('C4tech\Support\Repository', 'C4tech\Support\Model');
        $method = $this->getMethod('stubCreate');
        $method->invoke($this->object);

        // Ensure the repo is set
        $property = $this->getProperty('repo');
        $mocked_repo = $property->getValue($this->object);
        expect($mocked_repo instanceof MockInterface)->true();

        expect($mocked_repo->create())->true();
    }
}
