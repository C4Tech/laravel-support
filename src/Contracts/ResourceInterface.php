<?php namespace C4tech\Support\Contracts;

interface ResourceInterface
{
    /**
     * Get All
     *
     * Retrieve all accessible items of the resource. This should be a Collection
     * of items that implement this interface.
     * @return Illuminate\Support\Collection
     */
    public function getAll();

    /**
     * Find Or Fail
     *
     * Returns the resource item identified by $key or throw an error.
     * @param  int    $key Resource item identifier key.
     * @return static      Returned Resource should implement this interface.
     */
    public function findOrFail($key);

    /**
     * Create
     *
     * Transforms an array (i.e. from request input) into a new resource item.
     * @param  array $data Attributes of the new item.
     * @return static      Returned Resource should implement this interface.
     */
    public function create(array $data = []);

    /**
     * Update
     *
     * Patches an array of attributes (i.e. from request input) onto an existing
     * resource item and saves the item.
     * @param  array  $data Attributes to be updated.
     * @return bool
     */
    public function update(array $data = []);

    /**
     * Delete
     *
     * Remove an existing resource item.
     * @return bool
     */
    public function delete();
}
