<?php namespace C4tech\Support\Test;

use Mockery;
use PHPUnit_Framework_TestCase as TestCase;
use Codeception\Verify;
use C4tech\Test\Support\Traits\Reflectable;

class Base extends TestCase
{
    /**
     * Consume Reflectable Trait
     */
    use Reflectable;
}
