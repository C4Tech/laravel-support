<?php namespace C4tech\Support\Traits;

/**
 * Jsonable Column Trait
 *
 * Common methods for models with JSONable columns.
 */
trait JsonableColumn
{
    /**
     * Decode
     *
     * Decodes serialized or JSON-encoded string.
     * @param  string $field Saved data
     * @return mixed  Decoded data if successful, false otherwise
     */
    protected function decode($field)
    {
        if (is_null($field) || empty($field)) {
            return array();
        }

        return json_decode($field);
    }

    /**
     * Encode
     *
     * Encode data into a JSON-encoded string.
     * @param  mixed  $field Data to encode
     * @return string JSON-encoded object string
     */
    protected function encode($field)
    {
        return json_encode($field);
    }
}
