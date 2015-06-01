<?php namespace C4tech\Support\Traits;

use Illuminate\Support\Facades\Config;

/**
 * A foundation Model with useful features.
 */
trait JsonableApiModel
{
    /**
     * To Jsonable Array
     *
     * Returns an array of object properties converted to camelCase
     * for API handling.
     *
     * @return array
     */
    protected function toJsonableArray()
    {
        return (Config::get('c4tech.jsonify_output', true)) ?
            $this->convertToCamelCase($this->toArray()) :
            $this->toArray();
    }

    /**
     * Convert to Camel Case
     *
     * Converts array keys to camelCase, recursively.
     * @param  array  $array Original array
     * @return array
     */
    protected function convertToCamelCase($array)
    {
        $converted_array = [];
        foreach ($array as $old_key => $value) {
            if (is_array($value)) {
                $value = $this->convertToCamelCase($value);
            }

            $converted_array[camel_case($old_key)] = $value;
        }

        return $converted_array;
    }
}
