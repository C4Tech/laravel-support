<?php namespace C4tech\Test\Support;

use C4tech\Support\Model;
use C4tech\Support\Repository;
use Codeception\Verify;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Mockery;
use PHPUnit_Framework_TestCase as TestCase;
use ReflectionClass;
use stdClass;

class RepositoryTest extends TestCase
{
    protected $repo;

    public function setUp()
    {
        $this->repo = Mockery::mock('C4tech\Support\Repository')
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();
    }

    public function tearDown()
    {
        Mockery::close();
        Cache::clearResolvedInstances();
        Config::clearResolvedInstances();
        Log::clearResolvedInstances();
    }

    public function testBootNull()
    {
        expect_not($this->repo->boot());
    }

    public function testBootNoDebug()
    {
        $model = Mockery::mock('stdClass')
            ->makePartial();
        $model->shouldReceive('saved')
            ->once();
        $model->shouldReceive('deleted')
            ->once();

        Config::shouldReceive('get')
            ->with('app.debug')
            ->once()
            ->andReturn(false);
        Log::shouldReceive('info')
            ->never();

        $reflection = new ReflectionClass($this->repo);
        $property = $reflection->getProperty('model');
        $property->setAccessible(true);
        $property->setValue($this->repo, $model);

        expect_not($this->repo->boot());
    }

    public function testBootWithDebug()
    {
        $tag = 'test-123';

        $model = Mockery::mock('stdClass')
            ->makePartial();
        $model->id = 12;

        $model->shouldReceive('saved')
            ->with(Mockery::type('callable'))
            ->once();
        $model->shouldReceive('deleted')
            ->with(Mockery::type('callable'))
            ->once();

        Config::shouldReceive('get')
            ->with('app.debug')
            ->once()
            ->andReturn(true);
        Log::shouldReceive('info')
            ->with(Mockery::type('string'), ['model' => $model])
            ->once();

        $this->repo->shouldReceive('formatTag')
            ->with($model->id, 'object')
            ->andReturn($tag);

        $reflection = new ReflectionClass($this->repo);
        $property = $reflection->getProperty('model');
        $property->setAccessible(true);
        $property->setValue($model);

        expect_not($this->repo->boot());
    }

    public function testBootClosure()
    {
        $tag = 'test-123';

        $model = Mockery::mock('C4tech\Support\Model')
            ->makePartial();
        $model->id = 12;

        $model->shouldReceive('deleted');
        $model->shouldReceive('saved')
            ->with(
                Mockery::on(function ($method) use ($model) {
                    $method($model);
                })
            );

        Config::shouldReceive('get')
            ->with('app.debug')
            ->times(3)
            ->andReturn(true);
        Log::shouldReceive('info')
            ->with(Mockery::type('string'), ['model' => $model])
            ->once();
        Log::shouldReceive('debug')
            ->with(Mockery::type('string'), ['tag' => $tag])
            ->twice();
        Cache::shouldReceive('tags->flush')
            ->twice();

        $this->repo->shouldReceive('formatTag')
            ->with($model->id, 'object')
            ->andReturn($tag);

        $reflection = new ReflectionClass($this->repo);
        $property = $reflection->getProperty('model');
        $property->setAccessible(true);
        $property->setValue($model);

        expect_not($this->repo->boot());
    }

    public function testFindFailure()
    {
        $object_id = 11;
        $key = 'some-key';

        $this->repo->shouldReceive('formatTag')
            ->with($object_id)
            ->once()
            ->andReturn($key);

        $this->repo->shouldReceive('formatTag')
            ->with($object_id, 'object')
            ->once()
            ->andReturn($key);

        $query = Mockery::mock('stdClass');
        $query->shouldReceive('find')
            ->with($object_id)
            ->once()
            ->andReturn(null);

        $model = Mockery::mock('C4tech\Support\Model')
            ->makePartial();
        $model->shouldReceive('query->cacheTags->remember')
            ->andReturn($query);

        $reflection = new ReflectionClass($this->repo);
        $property = $reflection->getProperty('model');
        $property->setAccessible(true);
        $property->setValue($model);

        expect_not($this->repo->find($object_id));
    }

    public function testFindForced()
    {
        $object_id = 11;
        $key = 'some-key';

        $this->repo->shouldReceive('formatTag')
            ->with($object_id)
            ->once()
            ->andReturn($key);

        $this->repo->shouldReceive('formatTag')
            ->with($object_id, 'object')
            ->once()
            ->andReturn($key);

        $query = Mockery::mock('stdClass');
        $query->shouldReceive('find')
            ->with($object_id)
            ->once()
            ->andReturn(null);

        $model = Mockery::mock('C4tech\Support\Model')
            ->makePartial();
        $model->shouldReceive('query->cacheTags->remember')
            ->andReturn($query);

        $reflection = new ReflectionClass($this->repo);
        $property = $reflection->getProperty('model');
        $property->setAccessible(true);
        $property->setValue($model);

        $property = $reflection->getProperty('instances');
        $property->setAccessible(true);
        $instances = $property->getValue();
        $instances[$key] = true;
        $property->setValue($instances);

        expect_not($this->repo->find($object_id, true));
    }

    public function testFindSuccess()
    {
        $object_id = 11;
        $key = 'some-key';

        $this->repo->shouldReceive('formatTag')
            ->with($object_id)
            ->once()
            ->andReturn($key);

        $this->repo->shouldReceive('formatTag')
            ->with($object_id, 'object')
            ->once()
            ->andReturn($key);

        $object = Mockery::mock(new Model)
            ->makePartial();
        $object->exists = true;

        $query = Mockery::mock('stdClass');
        $query->shouldReceive('find')
            ->with($object_id)
            ->once()
            ->andReturn($object);

        $model = Mockery::mock('C4tech\Support\Model')
            ->makePartial();
        $model->shouldReceive('query->cacheTags->remember')
            ->andReturn($query);

        $reflection = new ReflectionClass($this->repo);
        $property = $reflection->getProperty('model');
        $property->setAccessible(true);
        $property->setValue($model);

        $this->repo->shouldReceive('make')
            ->with($object)
            ->once()
            ->andReturn(true);

        expect($this->repo->find($object_id))->true();
    }

    public function testFindCached()
    {
        $object_id = 11;
        $key = 'some-key';
        $object = Mockery::mock('C4tech\Support\Model');

        $reflection = new ReflectionClass($this->repo);
        $property = $reflection->getProperty('instances');
        $property->setAccessible(true);
        $instances = $property->getValue();
        $instances[$key] = $object;
        $property->setValue($instances);

        $this->repo->shouldReceive('formatTag')
            ->with($object_id)
            ->once()
            ->andReturn($key);

        $this->repo->shouldReceive('formatTag')
            ->with($object_id, 'object')
            ->once()
            ->andReturn($key);

        expect($this->repo->find($object_id))->equals($object);
    }

    public function testConstructor()
    {
        $model = new Model;

        $this->repo->shouldReceive('pullModel')
            ->once()
            ->andReturn($model);
        $this->repo->shouldReceive('pushCache')
            ->once();

        expect_not($this->repo->__construct());
        expect($this->repo->getModel())->equals($model);
    }

    public function testPullModelNull()
    {
        $reflection = new ReflectionClass($this->repo);

        $object_property = $reflection->getProperty('model');
        $object_property->setAccessible(true);
        $object_property->setValue('stdClass');

        $method = $reflection->getMethod('pullModel');
        $method->setAccessible(true);

        $results = $method->invoke($this->repo);
        expect(get_class($results))->equals('stdClass');
    }

    public function testPullModelGiven()
    {
        $object = Mockery::mock('C4tech\Support\Model');
        $object->exists = true;

        $reflection = new ReflectionClass($this->repo);

        $method = $reflection->getMethod('pullModel');
        $method->setAccessible(true);

        expect($method->invokeArgs($this->repo, [&$object]))->equals($object);
    }

    public function testPushCache()
    {
        $key = 'test-key-145';

        $this->repo->shouldReceive('getTags')
            ->once()
            ->andReturn([$key]);

        $object = new stdClass;
        $object->exists = true;

        $reflection = new ReflectionClass($this->repo);

        $method = $reflection->getMethod('pushCache');
        $method->setAccessible(true);

        $object_property = $reflection->getProperty('object');
        $object_property->setAccessible(true);
        $object_property->setValue($this->repo, $object);

        $instances_property = $reflection->getProperty('instances');
        $instances_property->setAccessible(true);
        $instances = $instances_property->getValue();
        expect($instances)->hasntKey($key);

        expect_not($method->invoke($this->repo));

        $instances = $instances_property->getValue();
        expect($instances)->hasKey($key);
        expect($instances[$key])->equals($this->repo);
    }

    public function testMake()
    {
        $mock = new Model;
        $mock->id = 14;

        $repo = new MockRepository();
        $new = $repo->make($mock);

        expect(get_class($new))->equals('C4tech\Test\Support\MockRepository');
    }

    public function testCreate()
    {
        $data = ['test' => true];
        $model_instance = Mockery::mock('C4tech\Support\Model');

        $model = Mockery::mock('C4tech\Support\Model[create]')
            ->shouldReceive('create')
            ->with($data)
            ->once()
            ->andReturn($model_instance)
            ->getMock();

        $reflection = new ReflectionClass($this->repo);
        $property = $reflection->getProperty('model');
        $property->setAccessible(true);
        $property->setValue($model);

        Log::shouldReceive('debug')
            ->with(
                Mockery::type('string'),
                [
                    'model' => $model,
                    'data' => $data
                ]
            )->once();

        $this->repo->shouldReceive('make')
            ->with($model_instance)
            ->once()
            ->andReturn(true);

        expect($this->repo->create($data))->true();
    }

    public function testUpdate()
    {
        $data = ['test' => true];
        $model = 'TestClass';

        $object = Mockery::mock('C4tech\Support\Model[update]')
            ->shouldReceive('update')
            ->with($data)
            ->once()
            ->andReturn(true)
            ->getMock();
        $object->id = 10;

        $reflection = new ReflectionClass($this->repo);
        $property = $reflection->getProperty('model');
        $property->setAccessible(true);
        $property->setValue($model);

        $instance = $reflection->getProperty('object');
        $instance->setAccessible(true);
        $instance->setValue($this->repo, $object);

        Log::shouldReceive('debug')
            ->with(
                Mockery::type('string'),
                [
                    'model' => $model,
                    'id' => $object->id,
                    'data' => $data
                ]
            )->once();

        expect($this->repo->update($data))->true();
    }

    public function testGetPresenter()
    {
        $object = Mockery::mock('C4tech\Support\Model[getPresenter]')
            ->shouldReceive('getPresenter')
            ->once()
            ->andReturn(true)
            ->getMock();

        $reflection = new ReflectionClass($this->repo);
        $instance = $reflection->getProperty('object');
        $instance->setAccessible(true);
        $instance->setValue($this->repo, $object);

        expect($this->repo->getPresenter())->true();
    }

    public function testGetTagsNull()
    {
        $tag = 'some-test-tag';
        $object = new stdClass;
        $object->id = 10;

        $reflection = new ReflectionClass($this->repo);
        $instance = $reflection->getProperty('object');
        $instance->setAccessible(true);
        $instance->setValue($this->repo, $object);

        $this->repo->shouldReceive('formatTag')
            ->with($object->id)
            ->once()
            ->andReturn($tag);

        expect($this->repo->getTags())->equals([$tag]);
    }

    public function testGetTagsSuffix()
    {
        $tag = 'some-test-tag';
        $tag_2 = 'another-tag';
        $object = new stdClass;
        $object->id = 10;

        $reflection = new ReflectionClass($this->repo);
        $instance = $reflection->getProperty('object');
        $instance->setAccessible(true);
        $instance->setValue($this->repo, $object);

        $this->repo->shouldReceive('formatTag')
            ->with($object->id)
            ->once()
            ->andReturn($tag);

        $this->repo->shouldReceive('formatTag')
            ->with($object->id, $tag)
            ->once()
            ->andReturn($tag_2);

        expect($this->repo->getTags($tag))->equals([$tag, $tag_2]);
    }

    public function testFormatTag()
    {
        $model = 'test';
        $oid = 5;
        $suffix = 'magic';

        $reflection = new ReflectionClass($this->repo);
        $property = $reflection->getProperty('model');
        $property->setAccessible(true);
        $property->setValue($model);

        $this->repo->shouldReceive('buildTag')
            ->with($model, $oid, $suffix)
            ->once()
            ->andReturn(true);

        expect($this->repo->formatTag($oid, $suffix))->true();
    }

    public function testBuildTagPlural()
    {
        $prefix = 'appendix';
        $reflection = new ReflectionClass($this->repo);
        $method = $reflection->getMethod('buildTag');
        $method->setAccessible(true);

        expect($method->invoke(null, $prefix))->equals(str_plural($prefix));
    }

    public function testBuildTagId()
    {
        $prefix = 'appendix';
        $oid = 19;

        $reflection = new ReflectionClass($this->repo);
        $method = $reflection->getMethod('buildTag');
        $method->setAccessible(true);

        expect($method->invoke(null, $prefix, $oid))->equals($prefix . '-' . $oid);
    }

    public function testBuildTagSuffic()
    {
        $prefix = 'appendix';
        $oid = 19;
        $suffix = 'test';

        $reflection = new ReflectionClass($this->repo);
        $method = $reflection->getMethod('buildTag');
        $method->setAccessible(true);

        expect($method->invoke(null, $prefix, $oid, $suffix))->equals($prefix . '-' . $oid . '-' . $suffix);
    }

    public function testGetMethod()
    {
        $this->repo->shouldReceive('getPresenter')
            ->once()
            ->andReturn(true);

        expect($this->repo->presenter)->true();
    }

    public function testGetProperty()
    {
        $object = new stdClass;
        $object->test = true;

        $reflection = new ReflectionClass($this->repo);
        $property = $reflection->getProperty('object');
        $property->setAccessible(true);
        $property->setValue($this->repo, $object);

        expect($this->repo->test)->true();
    }

    public function testSetMethod()
    {
        $mock = new Model;
        $mock->id = 14;

        $value = true;
        $repo = new MockRepository($mock);
        $repo->test = $value;

        $reflection = new ReflectionClass($repo);
        $property = $reflection->getProperty('something');
        $property->setAccessible(true);
        $something = $property->getValue($repo);

        expect($something)->equals($value);
    }

    public function testSetProperty()
    {
        $object = new stdClass;

        $reflection = new ReflectionClass($this->repo);
        $property = $reflection->getProperty('object');
        $property->setAccessible(true);
        $property->setValue($this->repo, $object);

        $this->repo->test = true;

        $model = $property->getValue($this->repo);

        expect($model->test)->true();
    }
}
