<?php namespace C4tech\Support\Test;

class Facade extends Base
{
    protected $facade;

    protected function verifyFacadeAccessor($accessor)
    {
        $facade = new $this->facade;
        $method = $this->getMethod($facade, 'getFacadeAccessor');

        expect($method->invoke(null))->equals($accessor);
    }
}
