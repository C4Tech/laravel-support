<?php namespace C4tech\Support\Test\Traits;

use ReflectionClass;
use stdClass;

trait Reflectable
{
    protected function getReflection(&$object)
    {
        return new ReflectionClass($object);
    }

    /**
     * Get Method
     *
     * @param  stdClass $object The object on which the method is defined
     * @param  string   $method The method name to retrieve
     * @return ReflectionMethod An invoke-able method
     */
    protected function getMethod(&$object, $method)
    {
        $reflection = $this->getReflection($object);
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
    protected function getProperty(&$object, $property)
    {
        $reflection = $this->getReflection($object);
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
    protected function getPropertyValue(&$object, $property)
    {
        $property = $this->getProperty($object, $property);
        return $property->getValue($object);
    }
}
