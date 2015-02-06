<?php namespace C4tech\Test\Support;

use Codeception\Verify;
use Mockery;
use PHPUnit_Framework_TestCase as TestCase;
use ReflectionClass;

/**
 * A foundation Presenter with useful features.
 */
class PresenterTest extends TestCase
{
    protected $presenter;

    public function setUp()
    {
        $this->presenter = Mockery::mock('C4tech\Support\Presenter')
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();
    }

    public function testPresentRepositoryNull()
    {
        $this->presenter->shouldReceive('setRepository')
            ->andReturn(true);

        expect($this->presenter->presentRepository())->true();
    }

    public function testPresentRepositoryValue()
    {
        $repo = 'TestClass';

        $reflection = new ReflectionClass($this->presenter);
        $property = $reflection->getProperty('repository_instance');
        $property->setAccessible(true);
        $property->setValue($this->presenter, $repo);

        expect($this->presenter->presentRepository())->equals($repo);
    }

    public function testSetRepositoryNull()
    {
        expect_not($this->presenter->setRepository());
    }

    public function testSetRepositoryValue()
    {
        $class = 'stdClass';

        $reflection = new ReflectionClass($this->presenter);
        $property = $reflection->getProperty('repository_class');
        $property->setAccessible(true);
        $property->setValue($this->presenter, $class);

        $repo = $this->presenter->setRepository();
        expect(get_class($repo))->equals($class);
    }
}
