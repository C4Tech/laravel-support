<?php namespace C4tech\Support\Contracts;

interface ResourceInterface
{
    /**
     * Get Model Class
     *
     * Central method to identify the underlying model's class.
     * @return string
     */
    public function getModelClass();

    /**
     * Make
     *
     * Create a new Repository with a live Model.
     * @param  \C4tech\Support\Contracts\ModelInterface $model The Model to wrap
     * @return static
     */
    public function make(ModelInterface $model);

    /**
     * Make Collection
     *
     * Tranform a collection of Models into a collection of Repositories.
     * @param  Collection|ModelInterface $models The models to transform
     * @return Collection
     */
    public function makeCollection($models);

    /**
     * Get Model
     *
     * Access the underlying model (necessary for methods associating relationships).
     * @return \C4tech\Support\Model
     */
    public function &getModel();

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
     * @param  int    $object_id The primary id of the object.
     * @return static
     * @throws Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function findOrFail($key);

    /**
     * Find
     *
     * Retrieve the resource item identified bo
     * @param  integer $object_id The primary id of the object.
     * @param  boolean $force     Force reloading the data?
     * @return static|void
     */
    public function &find($object_id, $force = false);

    /**
     * Create
     *
     * Transforms an array (i.e. from request input) into a new resource item.
     * @param  array $data Attributes of the new item.
     * @return static
     */
    public function create(array $data = []);

    /**
     * Update
     *
     * Patches an array of attributes (i.e. from request input) onto an existing
     * resource item and saves the item.
     * @param  array  $data Attributes to be updated.
     * @return integer|bool
     */
    public function update(array $data = []);

    /**
     * Delete
     *
     * Remove an existing resource item.
     * @return bool
     */
    public function delete();

    /**
     * Convert the model instance to JSON.
     *
     * @param  int  $options
     * @return string
     */
    public function toJson($options = 0);

    /**
     * Convert the object into something JSON serializable.
     *
     * @return array
     */
    public function jsonSerialize();

    /**
     * Convert the model instance to an array.
     *
     * @return array
     */
    public function toArray();
}
