<?php namespace C4tech\Test\Support\Test;

use Illuminate\Support\Facades\Facade as Base;

class MockFacade extends Base
{
    protected static function getFacadeAccessor()
    {
        return 'test.facade';
    }
}
