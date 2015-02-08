<?php namespace C4tech\Test\Support\Traits;

use Codeception\Verify;
use PHPUnit_Framework_TestCase as TestCase;
use ReflectionClass;
use stdClass;

class JsonableColumnTest extends TestCase
{
    public function setUp()
    {
        $this->trait = new MockModel;
    }

    protected function getMethod($method)
    {
        $reflection = new ReflectionClass($this->trait);
        $method = $reflection->getMethod($method);
        $method->setAccessible(true);
        return $method;
    }

    public function testDecode()
    {
        $jsonable = new stdClass;
        $jsonable->test = true;
        $jsonable->string = 'awyeah';
        $jsonable->array = [
            'still-test'
        ];

        $method = $this->getMethod('decode');
        expect($method->invoke($this->trait, null))->equals([]);
        expect($method->invoke($this->trait, ''))->equals([]);
        expect($method->invoke($this->trait, []))->equals([]);
        expect($method->invoke($this->trait, json_encode($jsonable)))->equals($jsonable);
    }

    public function testEncode()
    {
        $jsonable = new stdClass;
        $jsonable->test = true;
        $jsonable->string = 'awyeah';
        $jsonable->array = [
            'still-test'
        ];

        $method = $this->getMethod('encode');
        expect($method->invoke($this->trait, $jsonable))->equals(json_encode($jsonable));
    }
}
