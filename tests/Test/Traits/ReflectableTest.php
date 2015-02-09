<?php namespace C4tech\Test\Support\Test\Traits;

use C4tech\Support\Model;
use C4tech\Support\Test\Base;
use Mockery;
use PHPUnit_Framework_TestCase as TestCase;
use ReflectionClass;

class ReflectableTest extends TestCase
{
    public function setUp()
    {
        $this->trait = new Base;
        $this->trait->object = new Model;
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

    public function testGetReflection()
    {
        $method = $this->getMethod('getReflection');
        $reflection = $method->invokeArgs($this->trait, [&$this->trait->object]);
        expect(get_class($reflection))->equals('ReflectionClass');
    }

    public function testGetMethod()
    {
        $method_name = 'performDeleteOnModel';
        $method = $this->getMethod('getMethod');
        $reflection = $method->invokeArgs($this->trait, [&$this->trait->object, $method_name]);
        expect(get_class($reflection))->equals('ReflectionMethod');
        expect($reflection->name)->equals($method_name);
    }

    public function testGetProperty()
    {
        $property_name = 'guarded';
        $method = $this->getMethod('getProperty');
        $reflection = $method->invokeArgs($this->trait, [&$this->trait->object, $property_name]);
        expect(get_class($reflection))->equals('ReflectionProperty');
        expect($reflection->name)->equals($property_name);
    }

    public function testGetPropertyValue()
    {
        $property_name = 'unguarded';
        $property_value = false;
        $method = $this->getMethod('getPropertyValue');
        $value = $method->invokeArgs($this->trait, [&$this->trait->object, $property_name]);
        expect($value)->equals($property_value);
    }
}
