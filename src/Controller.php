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

    /**
     * Success
     *
     * Convenience wrapper for respond() to automatically set success
     * value and return a 200 response.
     * @param  boolean $success Success value (default is true)
     * @param  array   $headers Additional headers to send
     * @return \Illuminate\Http\Response   Laravel response
     */
    protected function success($success = true, $headers = [])
    {
        $this->data['success'] = $success;
        return $this->respond(200, $headers);
    }

    /**
     * Failure
     *
     * Convenience wrapper for respond() to automatically return a
     * failed response.
     * @param  integer $responseCode HTTP Response code (default is 500)
     * @param  array   $headers Additional headers to send
     * @return \Illuminate\Http\Response   Laravel response
     */
    protected function failure($responseCode = 500, $headers = [])
    {
        return $this->respond($responseCode, $headers);
    }
}
