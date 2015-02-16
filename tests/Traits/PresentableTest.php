<?php namespace C4tech\Test\Support\Traits;

use C4tech\Support\Model;
use Codeception\Verify;
use Illuminate\Support\Facades\Config;
use PHPUnit_Framework_TestCase as TestCase;
use ReflectionClass;

class PresentableTest extends TestCase
{
    public function setUp()
    {
        $this->trait = new Model;
    }

    public function tearDown()
    {
        Config::clearResolvedInstances();
    }

    public function testGetPresenterDefault()
    {
        $default_presenter = 'C4tech\Support\Presenter';
        Config::shouldReceive('get')
            ->with(null, $default_presenter)
            ->once()
            ->andReturn($default_presenter);

        $presenter = $this->trait->getPresenter();
        expect(get_class($presenter))->equals($default_presenter);
        expect($presenter->getObject())->equals($this->trait);
    }

    public function testGetPresenterCustom()
    {
        $presenter = 'stdClass';
        $default_presenter = 'C4tech\Support\Presenter';

        Config::shouldReceive('get')
            ->with(null, $default_presenter)
            ->once()
            ->andReturn($presenter);

        $presenter = $this->trait->getPresenter();
        expect(get_class($presenter))->equals('stdClass');
    }
}
