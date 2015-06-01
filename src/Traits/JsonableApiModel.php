<?php namespace C4tech\Support\Traits;

/**
 * A foundation Model with useful features.
 */
trait JsonableApiModel
{
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
