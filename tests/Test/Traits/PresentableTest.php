<?php namespace C4tech\Test\Support\Test\Traits;

use Codeception\Verify;
use Illuminate\Support\Facades\Config;
use Mockery;
use PHPUnit_Framework_TestCase as TestCase;
use ReflectionClass;

class PresentableTest extends TestCase
{
    public function setUp()
    {
        $this->trait = Mockery::mock('C4tech\Support\Test\Model')
            ->makePartial();
    }

    public function tearDown()
    {
        Config::clearResolvedInstances();
        Mockery::close();
    }

    public function testGetPresenter()
    {
        $class = 'C4tech\Support\Presenter';
        $model = new MockModel;

        $reflection = new ReflectionClass($this->trait);
        $setter = $reflection->getMethod('setModel');
        $setter->setAccessible(true);
        $setter->invoke($this->trait, $model);

        $method = $reflection->getMethod('verifyGetPresenter');
        $method->setAccessible(true);
        $method->invoke($this->trait, $class);
    }
}
