<?php namespace C4tech\Test\Foundation;

use Mockery;
use Codeception\Specify;
use Codeception\Verify;

class Base extends \PHPUnit_Framework_TestCase
{
    /**
     * Consume Codeception's Specify Trait
     */
    use Specify;

    /**
     * Get Method
     * 
     * @param  stdClass $object The object on which the method is defined
     * @param  string   $method The method name to retrieve
     * @return ReflectionMethod An invoke-able method
     */
    protected function getMethod(stdClass &$object, string $method)
    {
        $reflection = new \ReflectionClass($object);
        $property = $reflection->getMethod($method);
        $property->setAccessible(true);
        return $property;
    }

    /**
     * Get Property
     * @param  stdClass $object   The object on which the property is defined
     * @param  string   $property The property to retrieve
     * @return ReflectionProperty A publicly accessible property
     */
    protected function getProperty(stdClass &$object, string $property)
    {
        $reflection = new \ReflectionClass($object);
        $property = $reflection->getProperty($property);
        $property->setAccessible(true);
        return $property;
    }

    /**
     * Get Property Value
     * @param  stdClass $object   The object on which the property is defined
     * @param  string   $property The property to retrieve
     * @return mixed              The current value for the property
     */
    protected function getPropertyValue(stdClass &$object, string $property)
    {
        $property = $this->getProperty($object, $property);
        return $property->getValue($object);
    }

    /**
     * Set up for each spec
     */
    protected function setup()
    {
        $this->cleanSpecify();
        $this->afterSpecify(function () {
            Mockery::close();
        });
    }
}
