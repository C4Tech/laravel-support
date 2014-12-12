<?php namespace C4tech\Foundation;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Controller as BaseController;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Collection;

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
        'json' => [],
        'errors' => []
    ];

    /**
     * Simple Constructor
     */
    public function __construct()
    {
        Log::debug('Starting request for ' . Request::method() . ' ' . Route::current()->getUri());
    }

    /**
     * Respond
     *
     * Generates a response according to the detected format.
     * @param  string  $view    Full name of view
     * @param  integer $status  HTTP status code
     * @param  array   $headers Headers to pass to response
     * @return \Illuminate\Http\Response   Laravel response
     */
    protected function respond($view = '', $status = 200, $headers = [])
    {
        $format = Request::format('json');
        Log::debug('Responding via ' . $format, ['view' => $view, 'status' => $status]);
        switch ($format) {
            case 'html':
                $response = $this->respondHtml($view, $status, $headers);
                break;

            default:
                $response = $this->respondJson($status, $headers);
                break;
        }

        return $response;
    }

    /**
     * Respond JSON
     *
     * Generates a response forced into JSON.
     * @param  integer $status  HTTP status code
     * @param  array   $headers Headers to pass to response
     * @return \Illuminate\Http\Response   Laravel response
     */
    protected function respondJson($status = 200, $headers = [])
    {
        $json = (!empty($this->data['json'])) ? $this->data['json'] : $this->data;
        $response = Response::json($json, $status, $headers);

        return $response;
    }

    /**
     * Respond HTML
     *
     * Generates a response forced into HTML.
     * @param  string  $view   Full name of view
     * @param  integer $status HTTP status code
     * @param  array $headers Headers to pass to response
     * @return \Illuminate\Http\Response   Laravel response
     */
    protected function respondHtml($view = '', $status = 200, $headers = [])
    {
        if (!empty($view)) {
            $response = Response::view($view, $this->data, $status, $headers);
        } else {
            $response = Response::make(
                Collection::make($this->data)->flatten()->implode(0, "\n"),
                $status,
                $headers
            );
        }
        return $response;
    }

    /**
     * Setup the layout used by the controller.
     *
     * @return void
     */
    protected function setupLayout()
    {
        if (!is_null($this->layout)) {
            $this->layout = View::make($this->layout);
        }
    }
}
