<?php namespace C4tech\Test\Support\Test\Traits;

use Mockery;
use Mockery\MockInterface;
use PHPUnit_Framework_AssertionFailedError as AssertionException;
use PHPUnit_Framework_TestCase as TestCase;
use ReflectionClass;
use stdClass;

class ScopeableTest extends TestCase
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

    protected function setProperty($property, $value)
    {
        $reflection = new ReflectionClass($this->trait);
        $property = $reflection->getProperty($property);
        $property->setAccessible(true);
        $property->setValue($this->trait, $value);
    }

    public function testGetQueryMock()
    {

        $get = $this->getMethod('getQueryMock');
        $mock = $get->invoke($this->trait);

        expect($mock instanceof MockInterface)->true();
    }

    public function testScopeWhereDefault()
    {
        $model = new MockModel;
        $this->setProperty('model', $model);

        $method = 'scopeUserIs';
        $left = 'left';
        $right = 'right';

        $execute = $this->getMethod('verifyScopeWhere');
        $execute->invoke($this->trait, $method, $left, $right);
    }

    public function testScopeWhereParameters()
    {
        $model = new MockModel;
        $this->setProperty('model', $model);

        $method = 'scopeUserIsnt';
        $left = 'left';
        $param = 'right';
        $comp = '<>';
        $right = 'in ' . $param;

        $execute = $this->getMethod('verifyScopeWhere');
        $execute->invoke($this->trait, $method, $left, $right, $comp, $param);
    }
}
