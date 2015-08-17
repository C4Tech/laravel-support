<?php namespace C4tech\Test\Support\Traits;

use Codeception\Verify;
use Mockery;
use PHPUnit_Framework_TestCase as TestCase;
use ReflectionClass;

class JsonableApiModelTest extends TestCase
{
    public function testConvertToCamelCase()
    {
        $mock = new MockModel;
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
            'alreadyCamel' => true,
            'nestedArray' => [
                'nestedValue' => 23,
                'alreadyValue' => 42
            ],
            'uncamelText' => 'test',
            'alreadyNested' => [
                'nestedString' => 'awesome',
                'alreadyString' => 'nothing'
            ]
        ];

        $reflection = new ReflectionClass($mock);
        $method = $reflection->getMethod('convertToCamelCase');
        $method->setAccessible(true);

        expect($method->invoke($mock, $source))->equals($target);
        Mockery::close();
    }
}
