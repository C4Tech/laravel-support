<?php namespace C4tech\Test\Support\Traits;

use Illuminate\Http\Request;
use C4tech\Support\Traits\JsonableApiRequest;

class MockRequest extends Request
{
    use JsonableApiRequest;
}
