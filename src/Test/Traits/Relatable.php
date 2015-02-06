<?php namespace C4tech\Support\Test\Traits;

use Mockery;

trait Relatable
{
    /**
     * Consume the Modelable trait
     */
    use Modelable;

    /**
     * Verify BelongsTo
     *
     * Common test to ensure relationship method returns a BelongTo.
     * @param  string $method     Model method to test
     * @param  string $class      Class name of related Model
     * @param  string $foreignKey Name of foreign key on child table (e.g. user_id)
     * @param  string $otherKey   Name of primary key on parent table (e.g. id)
     * @return void
     */
    protected function verifyBelongsTo()
    {
        $this->verifyRelationship('belongsTo', func_get_args());
    }

    /**
     * Verify BelongsToMany
     *
     * Common test to ensure relationship method returns a BelongToMany.
     * @param  string $method   Model method to test
     * @param  string $class    Class name of related Model
     * @param  string $table    Name of pivot table
     * @param  string $leftKey  Name of foreign key on pivot table to this Model
     * @param  string $rightKey Name of foreign key on pivot table to other Model
     * @return void
     */
    protected function verifyBelongsToMany()
    {
        $this->verifyRelationship('belongsToMany', func_get_args());
    }

    /**
     * Verify HasMany
     *
     * Common test to ensure relationship method returns a HasMany.
     * @param  string $method     Model method to test
     * @param  string $class      Class name of related Model
     * @param  string $foreignKey Name of foreign key on child table (e.g. user_id)
     * @param  string $otherKey   Name of primary key on parent table (e.g. id)
     * @return void
     */
    protected function verifyHasMany()
    {
        $this->verifyRelationship('hasMany', func_get_args());
    }

    /**
     * Verify HasManyThrough
     *
     * Common test to ensure relationship method returns a HasManyThrough.
     * @param  string $method    Model method to test
     * @param  string $class     Class name of related Model
     * @param  string $through   Intermediate Model gluing the relationship
     * @param  string $firstKey  Name of foreign key on intermediate table to this Model
     * @param  string $secondKey Name of foreign key on distant table to intermediate Model
     * @return void
     */
    protected function verifyHasManyThrough()
    {
        $this->verifyRelationship('hasManyThrough', func_get_args());
    }

    /**
     * Verify HasOne
     *
     * Common test to ensure relationship method returns a HasOne.
     * @param  string $method     Model method to test
     * @param  string $class      Class name of related Model
     * @param  string $foreignKey Name of foreign key on child table (e.g. user_id)
     * @param  string $otherKey   Name of primary key on parent table (e.g. id)
     * @return void
     */
    protected function verifyHasOne()
    {
        $this->verifyRelationship('hasOne', func_get_args());
    }

    /**
     * Verify MorphMany
     *
     * Common test to ensure relationship method returns a MorphMany.
     * @param  string $method     Model method to test
     * @param  string $class      Class name of related Model
     * @param  string $name       Name of morphable relationship (e.g. imageable)
     * @param  string $morph_type Name of morph type column on foreign table (e.g. imageable_type)
     * @param  string $morph_id   Name of morph id column on foreign table (e.g. imageable_id)
     * @param  string $localKey   Name of primary key on this table
     * @return void
     */
    protected function verifyMorphMany()
    {
        $this->verifyRelationship('morphMany', func_get_args());
    }

    /**
     * Verify MorphOne
     *
     * Common test to ensure relationship method returns a MorphOne.
     * @param  string $method     Model method to test
     * @param  string $class      Class name of related Model
     * @param  string $name       Name of morphable relationship (e.g. imageable)
     * @param  string $morph_type Name of morph type column on foreign table (e.g. imageable_type)
     * @param  string $morph_id   Name of morph id column on foreign table (e.g. imageable_id)
     * @param  string $localKey   Name of primary key on this table
     * @return void
     */
    protected function verifyMorphOne()
    {
        $this->verifyRelationship('morphOne', func_get_args());
    }

    /**
     * Verify MorphTo
     *
     * Common test to ensure relationship method returns a MorphTo.
     * @param  string $method     Model method to test
     * @param  string $name       Name of morphable relationship (e.g. imageable)
     * @param  string $morph_type Name of morph type column on foreign table (e.g. imageable_type)
     * @param  string $morph_id   Name of morph id column on foreign table (e.g. imageable_id)
     * @return void
     */
    protected function verifyMorphTo()
    {
        $this->verifyRelationship('morphTo', func_get_args());
    }

    /**
     * Verify MorphToMany
     *
     * Common test to ensure relationship method returns a MorphToMany.
     * @param  string $method     Model method to test
     * @param  string $class      Class name of related Model
     * @param  string $name       Name of morphable relationship (e.g. imageable)
     * @param  string $table      Name of pivot table
     * @param  string $morphedKey Name of morph id column on pivot table (e.g. imageable_id)
     * @param  string $foreignKey Name of foreign key on pivot table to other Model (e.g. image_id)
     * @return void
     */
    protected function verifyMorphToMany()
    {
        $this->verifyRelationship('morphToMany', func_get_args());
    }

    /**
     * Verify MorphedByMany
     *
     * Common test to ensure relationship method returns a MorphedByMany.
     * @param  string $method     Model method to test
     * @param  string $class      Class name of related Model
     * @param  string $name       Name of morphable relationship (e.g. imageable)
     * @param  string $table      Name of pivot table
     * @param  string $foreignKey Name of foreign key on pivot table to this Model (e.g. image_id)
     * @param  string $morphedKey Name of morph id column on pivot table (e.g. imageable_id)
     * @return void
     */
    protected function verifyMorphedByMany()
    {
        $this->verifyRelationship('morphedByMany', func_get_args());
    }

    /**
     * Verify Relationship
     *
     * Method to route relationship tests to catch the correct number of arguments.
     * @param  string $relation Type of Relationship to ensure
     * @param  array  $args     Arguments to test
     * @return void
     */
    private function verifyRelationship($relation, $args)
    {
        $count = count($args);
        switch ($count) {
            case 7:
                $this->verifyRelationshipSix(
                    $args[0],
                    $relation,
                    $args[1],
                    $args[2],
                    $args[3],
                    $args[4],
                    $args[5],
                    $args[6]
                );
                break;
            case 6:
                $this->verifyRelationshipFive($args[0], $relation, $args[1], $args[2], $args[3], $args[4], $args[5]);
                break;
            case 5:
                $this->verifyRelationshipFour($args[0], $relation, $args[1], $args[2], $args[3], $args[4]);
                break;
            case 4:
                $this->verifyRelationshipThree($args[0], $relation, $args[1], $args[2], $args[3]);
                break;
            case 3:
                $this->verifyRelationshipTwo($args[0], $relation, $args[1], $args[2]);
                break;
            case 2:
                $this->verifyRelationshipOne($args[0], $relation, $args[1]);
                break;
            case 1:
                $this->verifyRelationshipZero($args[0], $relation);
                break;
            default:
                expect($count)->greaterOrEquals(1);
                expect($count)->lessOrEquals(7);
                break;
        }
    }

    /**
     * Verify Relationship Zero
     *
     * Test when relationship method expects no parameters.
     * @param  string $relation Type of Relationship method is called
     * @param  array  $args     Arguments to test
     * @return void
     */
    private function verifyRelationshipZero($method, $relation)
    {
        $model = $this->getModelMock();
        $model->shouldReceive($relation)
            ->withNoArgs()
            ->once()
            ->andReturn(true);
        expect($model->$method())->true();
    }

    /**
     * Verify Relationship One
     *
     * Test when relationship method expects one parameter.
     * @param  string $relation Type of Relationship method is called
     * @param  array  $args     Arguments to test
     * @param  mixed  $param_a  A parameter
     * @return void
     */
    private function verifyRelationshipOne($method, $relation, $param_a)
    {
        $model = $this->getModelMock();
        $model->shouldReceive($relation)
            ->with($param_a)
            ->once()
            ->andReturn(true);
        expect($model->$method())->true();
    }

    /**
     * Verify Relationship Two
     *
     * Test when relationship method expects two parameters.
     * @param  string $relation Type of Relationship method is called
     * @param  array  $args     Arguments to test
     * @param  mixed  $param_a  A parameter
     * @param  mixed  $param_b  A parameter
     * @return void
     */
    private function verifyRelationshipTwo($method, $relation, $param_a, $param_b)
    {
        $model = $this->getModelMock();
        $model->shouldReceive($relation)
            ->with($param_a, $param_b)
            ->once()
            ->andReturn(true);
        expect($model->$method())->true();
    }

    /**
     * Verify Relationship Three
     *
     * Test when relationship method expects three parameters.
     * @param  string $relation Type of Relationship method is called
     * @param  array  $args     Arguments to test
     * @param  mixed  $param_a  A parameter
     * @param  mixed  $param_b  A parameter
     * @param  mixed  $param_c  A parameter
     * @return void
     */
    private function verifyRelationshipThree(
        $method,
        $relation,
        $param_a,
        $param_b,
        $param_c
    ) {
        $model = $this->getModelMock();
        $model->shouldReceive($relation)
            ->with($param_a, $param_b, $param_c)
            ->once()
            ->andReturn(true);
        expect($model->$method())->true();
    }

    /**
     * Verify Relationship Four
     *
     * Test when relationship method expects four parameters.
     * @param  string $relation Type of Relationship method is called
     * @param  array  $args     Arguments to test
     * @param  mixed  $param_a  A parameter
     * @param  mixed  $param_b  A parameter
     * @param  mixed  $param_c  A parameter
     * @param  mixed  $param_d  A parameter
     * @return void
     */
    private function verifyRelationshipFour(
        $method,
        $relation,
        $param_a,
        $param_b,
        $param_c,
        $param_d
    ) {
        $model = $this->getModelMock();
        $model->shouldReceive($relation)
            ->with($param_a, $param_b, $param_c, $param_d)
            ->once()
            ->andReturn(true);
        expect($model->$method())->true();
    }

    /**
     * Verify Relationship Five
     *
     * Test when relationship method expects five parameters.
     * @param  string $relation Type of Relationship method is called
     * @param  array  $args     Arguments to test
     * @param  mixed  $param_a  A parameter
     * @param  mixed  $param_b  A parameter
     * @param  mixed  $param_c  A parameter
     * @param  mixed  $param_d  A parameter
     * @param  mixed  $param_e  A parameter
     * @return void
     */
    private function verifyRelationshipFive(
        $method,
        $relation,
        $param_a,
        $param_b,
        $param_c,
        $param_d,
        $param_e
    ) {
        $model = $this->getModelMock();
        $model->shouldReceive($relation)
            ->with($param_a, $param_b, $param_c, $param_d, $param_e)
            ->once()
            ->andReturn(true);
        expect($model->$method())->true();
    }

    /**
     * Verify Relationship Six
     *
     * Test when relationship method expects six parameters.
     * @param  string $relation Type of Relationship method is called
     * @param  array  $args     Arguments to test
     * @param  mixed  $param_a  A parameter
     * @param  mixed  $param_b  A parameter
     * @param  mixed  $param_c  A parameter
     * @param  mixed  $param_d  A parameter
     * @param  mixed  $param_e  A parameter
     * @param  mixed  $param_f  A parameter
     * @return void
     */
    private function verifyRelationshipSix(
        $method,
        $relation,
        $param_a,
        $param_b,
        $param_c,
        $param_d,
        $param_e,
        $param_f
    ) {
        $model = $this->getModelMock();
        $model->shouldReceive($relation)
            ->with($param_a, $param_b, $param_c, $param_d, $param_e, $param_f)
            ->once()
            ->andReturn(true);
        expect($model->$method())->true();
    }
}
