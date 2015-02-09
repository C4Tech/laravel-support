<?php namespace C4tech\Support\Test;

use Mockery;
use PHPUnit_Framework_TestCase as TestCase;
use Codeception\Verify;
use C4tech\Support\Test\Traits\Reflectable;

class Base extends TestCase
{
    /**
     * Consume Reflectable Trait
     */
    use Reflectable;

    /**
     * @codeCoverageIgnore
    */
    public function tearDown()
    {
        Mockery::close();
    }
}
