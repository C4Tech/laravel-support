<?php namespace C4tech\Support\Traits;

use Illuminate\Support\Facades\Config;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * A foundation Model with useful features.
 *
 * @property ParameterBad $json JSON input
 */
trait JsonableApiRequest
{
    /**
     * Common handler to use config for dis-/en-abling JSON casing.
     *
     * @param  string  $key
     * @param  mixed   $default
     * @return mixed
     */
    public function transformJson($key = null, $default = null)
    {
        return (Config::get('c4tech.snakify_json_input', true)) ?
            $this->snakeJson($key, $default) :
            parent::json($key, $default);
    }

    /**
     * Transform the JSON payload for the request so that the object
     * keys are snake_case rather than camelCase.
     *
     * @param  string  $key
     * @param  mixed   $default
     * @return mixed
     */
    public function snakeJson($key = null, $default = null)
    {
        if (!isset($this->json)) {
            $parameters = $this->convertToSnakeCase(json_decode($this->getContent(), true));

            $this->json = new ParameterBag($parameters);
        }

        if (is_null($key)) {
            return $this->json;
        }

        return array_get($this->json->all(), $key, $default);
    }

    /**
     * Convert to Snake Case
     *
     * Converts array keys to snake_case, recursively.
     * @param  array  $array Original array
     * @return array
     */
    protected function convertToSnakeCase($array)
    {
        $converted_array = [];
        foreach ($array as $old_key => $value) {
            if (is_array($value)) {
                $value = $this->convertToSnakeCase($value);
            }
            $converted_array[snake_case($old_key)] = $value;
        }

        return $converted_array;
    }
}
