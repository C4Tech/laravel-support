<?php namespace C4tech\Support;

use C4tech\Support\Traits\JsonableApiRequest;
use Illuminate\Http\Request as BaseRequest;

abstract class Request extends BaseRequest
{
    use JsonableApiRequest;

    /**
     * Override the default JSON handling method to convert camelCase to snake_case.
     * @inheritDoc
     */
    public function json($key = null, $default = null)
    {
        return $this->transformJson($key, $default);
    }
}
