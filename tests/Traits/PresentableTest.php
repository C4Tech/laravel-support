<?php namespace C4tech\Test\Support\Traits;

use C4tech\Support\Model;
use Codeception\Verify;
use PHPUnit_Framework_TestCase as TestCase;
use ReflectionClass;

class PresentableTest extends TestCase
{
    public function setUp()
    {
        $this->trait = new Model;
    }

    public function testGetPresenterDefault()
    {
        $presenter = $this->trait->getPresenter();
        expect(get_class($presenter))->equals('C4tech\Support\Presenter');
        expect($presenter->getObject())->equals($this->trait);
    }

    public function testGetPresenterCustom()
    {
        $reflection = new ReflectionClass($this->trait);
        $property = $reflection->getProperty('presenter');
        $property->setAccessible(true);
        $property->setValue('stdClass');
        $presenter = $this->trait->getPresenter();
        expect(get_class($presenter))->equals('stdClass');
    }
}
