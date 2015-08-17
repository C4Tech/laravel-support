<?php namespace C4tech\Test\Support;

use Codeception\Verify;
use Mockery;
use PHPUnit_Framework_TestCase as TestCase;
use ReflectionClass;

class ServiceProviderTest extends TestCase
{
    protected $provider;

    public function setUp()
    {
        include_once('helpers.php');
        $this->provider = Mockery::mock('C4tech\Support\ServiceProvider', [null])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();
    }

    public function tearDown()
    {
        Mockery::close();
    }

    public function testConstructor()
    {
        $reflection = new ReflectionClass($this->provider);
        $property = $reflection->getProperty('configPath');
        $property->setAccessible(true);
        expect($property->getValue($this->provider))
            ->contains('/resources/config.php');
    }

    public function testBoot()
    {
        $this->provider->shouldReceive('publishes')
            ->with(Mockery::on(function ($configMapping) {
                $key = array_pop(array_keys($configMapping));
                $value = array_pop($configMapping);
                expect($key)->contains('/resources/config.php');
                expect($value)->equals('test/c4tech.php');

                return true;
            }))->once();

        expect_not($this->provider->boot());
    }

    public function testRegister()
    {
        $this->provider->shouldReceive('mergeConfigFrom')
            ->with(Mockery::type('string'), 'c4tech')
            ->once();

        expect_not($this->provider->register());
    }
}
