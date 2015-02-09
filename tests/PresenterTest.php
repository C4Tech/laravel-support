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

    public function tearDown()
    {
        Mockery::close();
    }

    public function testPresentRepoNull()
    {
        $this->presenter->shouldReceive('setRepository')
            ->andReturn(true);

        expect($this->presenter->presentRepo())->true();
    }

    public function testPresentRepoValue()
    {
        $repo = 'TestClass';

        $reflection = new ReflectionClass($this->presenter);
        $property = $reflection->getProperty('instance');
        $property->setAccessible(true);
        $property->setValue($this->presenter, $repo);

        expect($this->presenter->presentRepo())->equals($repo);
    }

    public function testSetRepositoryNull()
    {
        expect_not($this->presenter->setRepository());
    }

    public function testSetRepositoryValue()
    {
        $class = 'stdClass';

        $reflection = new ReflectionClass($this->presenter);
        $property = $reflection->getProperty('repository');
        $property->setAccessible(true);
        $property->setValue($this->presenter, $class);

        $repo = $this->presenter->setRepository();
        expect(get_class($repo))->equals($class);
    }
}
