<?php namespace C4tech\Test\Support\Test\Traits;

use Mockery;
use Mockery\MockInterface;
use PHPUnit_Framework_AssertionFailedError as AssertionException;
use PHPUnit_Framework_TestCase as TestCase;
use ReflectionClass;
use stdClass;

class ModelableTest extends TestCase
{
    public function setUp()
    {
        $this->trait = Mockery::mock('C4tech\Support\Test\Model')
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();
    }

    public function tearDown()
    {
        Mockery::close();
    }

    protected function getMethod($method)
    {
        $reflection = new ReflectionClass($this->trait);
        $method = $reflection->getMethod($method);
        $method->setAccessible(true);
        return $method;
    }

    protected function getProperty($property)
    {
        $reflection = new ReflectionClass($this->trait);
        $property = $reflection->getProperty($property);
        $property->setAccessible(true);
        return $property;
    }

    public function testSetModelModel()
    {
        $method = $this->getMethod('setModel');
        $model = Mockery::mock('C4tech\Support\Model');

        $method->invoke($this->trait, $model);
        $property = $this->getProperty('model');
        $value = $property->getValue($this->trait);
        expect($value)->equals($model);
    }

    public function testSetModelString()
    {
        $method = $this->getMethod('setModel');
        $model = 'C4tech\Support\Model';

        $method->invoke($this->trait, $model);
        $property = $this->getProperty('model');
        $value = $property->getValue($this->trait);
        expect(get_class($value))->equals($model);
    }

    public function testSetModelNoClass()
    {
        $method = $this->getMethod('setModel');
        $model = 'Test\Model';
        $success = false;

        try {
            $method->invoke($this->trait, $model);
        } catch (AssertionException $error) {
            $success = true;
        }

        expect($success)->true();
    }

    public function testSetModelNotModel()
    {
        $method = $this->getMethod('setModel');
        $model = new stdClass;
        $success = false;

        try {
            $method->invoke($this->trait, $model);
        } catch (AssertionException $error) {
            $success = true;
        }

        expect($success)->true();
    }

    public function testSetModelArray()
    {
        $method = $this->getMethod('setModel');
        $model = [];
        $success = false;

        try {
            $method->invoke($this->trait, $model);
        } catch (AssertionException $error) {
            $success = true;
        }

        expect($success)->true();
    }

    public function testGetModelMock()
    {
        $set = $this->getMethod('setModel');
        $model = 'C4tech\Support\Model';
        $set->invoke($this->trait, $model);

        $get = $this->getMethod('getModelMock');
        $mock = $get->invoke($this->trait);

        expect($mock instanceof MockInterface)->true();
    }
}
