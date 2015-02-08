<?php namespace C4tech\Test\Support\Traits;

use Carbon\Carbon;
use Codeception\Verify;
use Mockery;
use PHPUnit_Framework_TestCase as TestCase;
use ReflectionClass;

class DateFilterTest extends TestCase
{
    public function setUp()
    {
        $this->trait = Mockery::mock('C4tech\Support\Model')
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();
    }

    protected function verifyScopeStatement($method, $when, $comp = '=')
    {
        $query = true;
        $result = true;
        $date = 'when';
        $default_date = 'now';

        $this->trait->shouldReceive($when)
            ->with($query, $default_date, $comp)
            ->once()
            ->andReturn($result);

        expect($this->trait->$method($query))->equals($result);

        $this->trait->shouldReceive($when)
            ->with($query, $date, $comp)
            ->once()
            ->andReturn($result);

        expect($this->trait->$method($query, $date))->equals($result);
    }

    public function testCreatedBefore()
    {
        $this->verifyScopeStatement('scopeCreatedBefore', 'whenCreated', '<');
    }

    public function testCreatedOnOrBefore()
    {
        $this->verifyScopeStatement('scopeCreatedOnOrBefore', 'whenCreated', '<=');
    }

    public function testCreatedOnOrAfter()
    {
        $this->verifyScopeStatement('scopeCreatedOnOrAfter', 'whenCreated', '>=');
    }

    public function testCreatedAfter()
    {
        $this->verifyScopeStatement('scopeCreatedAfter', 'whenCreated', '>');
    }

    public function testUpdatedBefore()
    {
        $this->verifyScopeStatement('scopeUpdatedBefore', 'whenUpdated', '<');
    }

    public function testUpdatedOnOrBefore()
    {
        $this->verifyScopeStatement('scopeUpdatedOnOrBefore', 'whenUpdated', '<=');
    }

    public function testUpdatedOnOrAfter()
    {
        $this->verifyScopeStatement('scopeUpdatedOnOrAfter', 'whenUpdated', '>=');
    }

    public function testUpdatedAfter()
    {
        $this->verifyScopeStatement('scopeUpdatedAfter', 'whenUpdated', '>');
    }

    protected function verifyWhenStatement($method, $field)
    {
        $query = 'test';
        $date = 'when';
        $default_date = 'now';
        $comp = '=';
        $default_comp = '>=';
        $result = false;

        $reflection = new ReflectionClass($this->trait);
        $caller = $reflection->getMethod($method);
        $caller->setAccessible(true);

        $this->trait->shouldReceive('whenOn')
            ->with($query, $field, $default_date, $default_comp)
            ->once()
            ->andReturn($result);

        expect($caller->invoke($this->trait, $query))->equals($result);

        $this->trait->shouldReceive('whenOn')
            ->with($query, $field, $date, $comp)
            ->once()
            ->andReturn($result);

        expect($caller->invoke($this->trait, $query, $date, $comp))->equals($result);
    }

    public function testWhenCreated()
    {
        $this->verifyWhenStatement('whenCreated', 'created_at');
    }

    public function testWhenUpdated()
    {
        $this->verifyWhenStatement('whenUpdated', 'updated_at');
    }

    public function testWhenOn()
    {
        $field = 'tested_at';
        $date = Carbon::parse('2005-08-27');
        Carbon::setTestNow($date);
        $comp = '=';
        $default_comp = '>=';
        $result = false;

        $reflection = new ReflectionClass($this->trait);
        $caller = $reflection->getMethod('whenOn');
        $caller->setAccessible(true);

        $query = Mockery::mock('stdClass');
        $query->shouldReceive('where')
            ->with($field, $default_comp, $date->toDateString())->once()
            ->andReturn($result);

        expect($caller->invoke($this->trait, $query, $field))->equals($result);

        $query = Mockery::mock('stdClass');
        $query->shouldReceive('where')
            ->with($field, $comp, $date->toDateString())
            ->once()
            ->andReturn($result);

        expect($caller->invoke($this->trait, $query, $field, $date, $comp))->equals($result);
    }
}
