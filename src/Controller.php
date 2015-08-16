<?php namespace C4tech\Support;

use Illuminate\Support\Facades\Response;
use Illuminate\Routing\Controller as BaseController;

/**
 * A foundation Controller with useful features.
 */
class Controller extends BaseController
{
    /**
     * Output data collector.
     * @var array
     */
    protected $data = [
        'success' => false,
        'errors' => []
    ];

    /**
     * Respond
     *
     * Generates a response according to the detected format.
     * @param  string  $view    Full name of view
     * @param  integer $status  HTTP status code
     * @param  array   $headers Headers to pass to response
     * @return \Illuminate\Http\Response   Laravel response
     */
    protected function respond($status = 200, $headers = [])
    {
        return Response::json($this->data, $status, $headers);
    }
}
