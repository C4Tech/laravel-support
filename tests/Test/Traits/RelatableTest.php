<?php namespace C4tech\Test\Support\Test\Traits;

use Mockery;
use Mockery\MockInterface;
use PHPUnit_Framework_AssertionFailedError as AssertionException;
use PHPUnit_Framework_TestCase as TestCase;
use ReflectionClass;
use stdClass;

class RelatableTest extends TestCase
{
    public function setUp()
    {
        $this->trait = Mockery::mock('C4tech\Support\Test\Model')
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();
    }

    public function tearDown()
    {
        Mockery::close();
    }

    protected function getMethod($method)
    {
        $reflection = new ReflectionClass($this->trait);
        $method = $reflection->getMethod($method);
        $method->setAccessible(true);
        return $method;
    }

    protected function verifyCallToRelationship($method, $relation)
    {
        $method = $this->getMethod($method);
        $this->trait->shouldReceive('verifyRelationship')
            ->with($relation, Mockery::type('array'))
            ->once()
            ->andReturn(true);
        expect_not($method->invoke($this->trait, 'test'));
    }

    protected function verifyRelationshipCall($method, $model_method, $relation, $params = [])
    {
        $model = Mockery::mock('C4tech\Test\Support\Test\Traits\MockModel')
            ->makePartial();
        $this->trait->shouldReceive('getModelMock')
            ->withNoArgs()
            ->once()
            ->andReturn($model);

        $method = $this->getMethod($method);
        $args = [$model_method, $relation];
        $args = array_merge($args, $params);

        expect_not($method->invokeArgs($this->trait, $args));
    }

    protected function verifyRelationshipCallCallback($method, $model_method, $relation, $params = [])
    {
        $model = Mockery::mock('C4tech\Test\Support\Test\Traits\MockModel')
            ->makePartial();
        $this->trait->shouldReceive('getModelMock')
            ->withNoArgs()
            ->once()
            ->andReturn($model);

        $method = $this->getMethod($method);
        $args = [$model_method, $relation];
        $args = array_merge($args, $params);
        $args[] = function ($mock) {
            expect($mock instanceof MockInterface)->true();
        };

        expect_not($method->invokeArgs($this->trait, $args));
    }

    public function testBelongsTo()
    {
        $this->verifyCallToRelationship('verifyBelongsTo', 'belongsTo');
    }

    public function testBelongsToMany()
    {
        $this->verifyCallToRelationship('verifyBelongsToMany', 'belongsToMany');
    }

    public function testHasMany()
    {
        $this->verifyCallToRelationship('verifyHasMany', 'hasMany');
    }

    public function testHasManyThrough()
    {
        $this->verifyCallToRelationship('verifyHasManyThrough', 'hasManyThrough');
    }

    public function testHasOne()
    {
        $this->verifyCallToRelationship('verifyHasOne', 'hasOne');
    }

    public function testMorphMany()
    {
        $this->verifyCallToRelationship('verifyMorphMany', 'morphMany');
    }

    public function testMorphOne()
    {
        $this->verifyCallToRelationship('verifyMorphOne', 'morphOne');
    }

    public function testMorphTo()
    {
        $this->verifyCallToRelationship('verifyMorphTo', 'morphTo');
    }

    public function testMorphToMany()
    {
        $this->verifyCallToRelationship('verifyMorphToMany', 'morphToMany');
    }

    public function testMorphedByMany()
    {
        $this->verifyCallToRelationship('verifyMorphedByMany', 'morphedByMany');
    }

    public function testRelationship()
    {
        $relation = 'test';
        $method = $this->getMethod('verifyRelationship');

        $this->trait->shouldReceive('verifyRelationshipFive')
            ->with(1, $relation, 2, 3, 4, 5, 6, null)
            ->once()
            ->andReturn(true);
        $this->trait->shouldReceive('verifyRelationshipFour')
            ->with(1, $relation, 2, 3, 4, 5, null)
            ->once()
            ->andReturn(true);
        $this->trait->shouldReceive('verifyRelationshipThree')
            ->with(1, $relation, 2, 3, 4, null)
            ->once()
            ->andReturn(true);
        $this->trait->shouldReceive('verifyRelationshipTwo')
            ->with(1, $relation, 2, 3, null)
            ->once()
            ->andReturn(true);
        $this->trait->shouldReceive('verifyRelationshipOne')
            ->with(1, $relation, 2, null)
            ->once()
            ->andReturn(true);
        $this->trait->shouldReceive('verifyRelationshipZero')
            ->with(1, $relation, null)
            ->once()
            ->andReturn(true);

        $args = [1, 2, 3, 4, 5, 6, 7];

        $success = false;
        try {
            expect_not($method->invoke($this->trait, $relation, $args));
        } catch (AssertionException $error) {
            $success = true;
        }
        expect('Throws error on more than 6 args', $success)->true();
        array_pop($args);

        do {
            expect_not($method->invoke($this->trait, $relation, $args));
            array_pop($args);
        } while (count($args));

        $success = false;
        try {
            expect_not($method->invoke($this->trait, $relation, $args));
        } catch (AssertionException $error) {
            $success = true;
        }
        expect('Throws error on fewer than 1 arg', $success)->true();
    }

    public function testRelationshipZero()
    {
        $this->verifyRelationshipCall('verifyRelationshipZero', 'thing', 'morphTo');
    }

    public function testRelationshipOne()
    {
        $this->verifyRelationshipCall('verifyRelationshipOne', 'owner', 'hasOne', ['User']);
    }

    public function testRelationshipTwo()
    {
        $this->verifyRelationshipCall('verifyRelationshipTwo', 'photos', 'hasMany', ['Photo', 'photo_id']);
    }

    public function testRelationshipThree()
    {
        $this->verifyRelationshipCall('verifyRelationshipThree', 'users', 'belongsTo', ['User', 'user_id', 'id']);
    }

    public function testRelationshipFour()
    {
        $this->verifyRelationshipCall(
            'verifyRelationshipFour',
            'posts',
            'hasManyThrough',
            ['Post', 'User', 'model_id', 'post_id']
        );
    }

    public function testRelationshipFive()
    {
        $this->verifyRelationshipCall(
            'verifyRelationshipFive',
            'photo',
            'morphOne',
            ['User', 'imageable', 'imageable_type', 'imageable_id', 'id']
        );
    }

    public function testRelationshipZeroCallback()
    {
        $this->verifyRelationshipCallCallback('verifyRelationshipZero', 'thing', 'morphTo');
    }

    public function testRelationshipOneCallback()
    {
        $this->verifyRelationshipCallCallback('verifyRelationshipOne', 'owner', 'hasOne', ['User']);
    }

    public function testRelationshipTwoCallback()
    {
        $this->verifyRelationshipCallCallback('verifyRelationshipTwo', 'photos', 'hasMany', ['Photo', 'photo_id']);
    }

    public function testRelationshipThreeCallback()
    {
        $this->verifyRelationshipCallCallback(
            'verifyRelationshipThree',
            'users',
            'belongsTo',
            ['User', 'user_id', 'id']
        );
    }

    public function testRelationshipFourCallback()
    {
        $this->verifyRelationshipCallCallback(
            'verifyRelationshipFour',
            'posts',
            'hasManyThrough',
            ['Post', 'User', 'model_id', 'post_id']
        );
    }

    public function testRelationshipFiveCallback()
    {
        $this->verifyRelationshipCallCallback(
            'verifyRelationshipFive',
            'photo',
            'morphOne',
            ['User', 'imageable', 'imageable_type', 'imageable_id', 'id']
        );
    }
}
