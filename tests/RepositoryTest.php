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
        $this->repo->shouldReceive('getModelClass')
            ->withNoArgs()
            ->once()
            ->andReturn(null);

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

        $this->repo->shouldReceive('getModelClass')
            ->withNoArgs()
            ->once()
            ->andReturn($model);

        Log::shouldReceive('info')
            ->never();

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

        $this->repo->shouldReceive('getModelClass')
            ->withNoArgs()
            ->once()
            ->andReturn($model);

        Config::shouldReceive('get')
            ->with('app.debug')
            ->once()
            ->andReturn(true);
        Log::shouldReceive('info')
            ->with(Mockery::type('string'), Mockery::type('array'))
            ->once();

        $this->repo->shouldReceive('formatTag')
            ->with($model->id, 'object')
            ->andReturn($tag);

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
                    return true;
                })
            );

        $this->repo->shouldReceive('getModelClass')
            ->withNoArgs()
            ->once()
            ->andReturn($model);

        Config::shouldReceive('get')
            ->with('app.debug')
            ->twice()
            ->andReturn(true);
        Log::shouldReceive('info')
            ->with(Mockery::type('string'), Mockery::type('array'))
            ->once();
        Log::shouldReceive('debug')
            ->with(Mockery::type('string'), Mockery::type('array'))
            ->once();
        Cache::shouldReceive('tags->flush')
            ->once();

        $this->repo->shouldReceive('formatTag')
            ->with($model->id, 'object')
            ->andReturn($tag);

        expect_not($this->repo->boot());
    }

    public function testFindOrFailSucceeds()
    {
        $this->repo->shouldReceive('find')
            ->andReturn(true);

        expect($this->repo->findOrFail(1))->true();
    }

    /**
     * @expectedException Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function testFindOrFailFails()
    {
        $this->repo->shouldReceive('find')
            ->andReturnNull();

        expect_not($this->repo->findOrFail(1));
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

        $model = Mockery::mock('C4tech\Support\Model')
            ->makePartial();
        $model->shouldReceive('find')
            ->with($object_id)
            ->once()
            ->andReturnNull();

        $this->repo->shouldReceive('getModelClass')
            ->withNoArgs()
            ->once()
            ->andReturn($model);

        $this->repo->shouldReceive('getCacheId')
            ->with('object', $object_id)
            ->once()
            ->andReturn('hash');

        Cache::shouldReceive('tags->remember')
            ->with(Mockery::type('array'))
            ->with(Mockery::type('string'), 10, Mockery::on(function ($closure) use ($model, $object_id) {
                expect($closure())->null();

                return true;
            }))->once();

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

        $model = Mockery::mock('C4tech\Support\Model')
            ->makePartial();
        $model->shouldReceive('__toString')
            ->andReturn('model');
        $model->shouldReceive('find')
            ->with($object_id)
            ->once()
            ->andReturn(null);

        $this->repo->shouldReceive('getModelClass')
            ->withNoArgs()
            ->once()
            ->andReturn($model);

        $this->repo->shouldReceive('getCacheId')
            ->with('object', $object_id)
            ->once()
            ->andReturn('hash');

        Cache::shouldReceive('tags->remember')
            ->with(Mockery::type('array'))
            ->with(Mockery::type('string'), 10, Mockery::on(function ($closure) {
                expect($closure())->null();

                return true;
            }))->once();

        $reflection = new ReflectionClass($this->repo);
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

        $model = Mockery::mock('C4tech\Support\Model')
            ->makePartial();
        $model->shouldReceive('__toString')
            ->andReturn('model');
        $model->shouldReceive('find')
            ->with($object_id)
            ->once()
            ->andReturn($object);

        $this->repo->shouldReceive('getModelClass')
            ->withNoArgs()
            ->once()
            ->andReturn($model);

        $this->repo->shouldReceive('getCacheId')
            ->with('object', $object_id)
            ->once()
            ->andReturn('hash');

        Cache::shouldReceive('tags->remember')
            ->with(Mockery::type('array'))
            ->with(Mockery::type('string'), Mockery::type('integer'), Mockery::on(function ($closure) use ($object) {
                expect($closure())->equals($object);

                return true;
            }))->once()
            ->andReturn($object);

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
        $model = 'stdClass';
        $reflection = new ReflectionClass($this->repo);

        $this->repo->shouldReceive('getModelClass')
            ->withNoArgs()
            ->once()
            ->andReturn($model);

        $method = $reflection->getMethod('pullModel');
        $method->setAccessible(true);

        $results = $method->invoke($this->repo);
        expect(get_class($results))->equals($model);
    }

    public function testPullModelGiven()
    {
        $object = Mockery::mock('C4tech\Support\Model');
        $object->exists = true;

        $this->repo->shouldReceive('getModelClass')
            ->withNoArgs()
            ->once()
            ->andReturn(null);

        $reflection = new ReflectionClass($this->repo);

        $method = $reflection->getMethod('pullModel');
        $method->setAccessible(true);

        expect($method->invokeArgs($this->repo, [&$object]))->equals($object);
    }

    public function testPushCache()
    {
        $key = 'test-key-145';
        $id = 145;

        $this->repo->shouldReceive('formatTag')
            ->with($id)
            ->once()
            ->andReturn($key);

        $object = new stdClass;
        $object->exists = true;
        $object->id = $id;

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
        $model = 'C4tech\Support\Model';

        Config::shouldReceive('get')
            ->with($model, $model)
            ->times(4)
            ->andReturn($model);

        $repo = new MockRepository;

        \PHPUnit_Framework_Assert::assertInstanceOf('C4tech\Support\Repository', $repo->make($mock));
    }

    public function testGetModelClass()
    {
        Config::shouldReceive('get')
            ->with(null, null)
            ->once()
            ->andReturn(true);

        expect($this->repo->getModelClass())->true();
    }

    public function testGetCacheIdDefault()
    {
        $this->repo->shouldReceive('getModelClass')
            ->withNoArgs()
            ->once()
            ->andReturn('class');

        expect($this->repo->getCacheId('test'))->equals(md5('classtest'));
    }

    public function testGetCacheIdParam()
    {
        $this->repo->shouldReceive('getModelClass')
            ->withNoArgs()
            ->once()
            ->andReturn('class');

        expect($this->repo->getCacheId('test', 5))->equals(md5('class5test'));
    }

    public function testGetCacheIdModel()
    {
        $model = new stdClass;
        $model->exists = true;
        $model->id = 4;

        $reflection = new ReflectionClass($this->repo);
        $instance = $reflection->getProperty('object');
        $instance->setAccessible(true);
        $instance->setValue($this->repo, $model);

        $this->repo->shouldReceive('getModelClass')
            ->withNoArgs()
            ->once()
            ->andReturn('class');

        expect($this->repo->getCacheId('test'))->equals(md5('class4test'));
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

        $this->repo->shouldReceive('getModelClass')
            ->withNoArgs()
            ->once()
            ->andReturn($model);

        Log::shouldReceive('debug')
            ->with(Mockery::type('string'), Mockery::type('array'))
            ->once();

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

        $this->repo->shouldReceive('getModelClass')
            ->withNoArgs()
            ->once()
            ->andReturn($model);

        $reflection = new ReflectionClass($this->repo);
        $instance = $reflection->getProperty('object');
        $instance->setAccessible(true);
        $instance->setValue($this->repo, $object);

        Log::shouldReceive('debug')
            ->with(Mockery::type('string'), Mockery::type('array'))
            ->once();

        expect($this->repo->update($data))->true();
    }

    public function testDelete()
    {
        $model = 'TestClass';
        $instances = [
            'tag' => 'A TestClass Instance'
        ];

        $object = Mockery::mock('C4tech\Support\Model[delete]')
            ->shouldReceive('delete')
            ->once()
            ->andReturn(true)
            ->getMock();
        $object->id = 10;

        $this->repo->shouldReceive('getModelClass')
            ->withNoArgs()
            ->once()
            ->andReturn($model);

        Log::shouldReceive('debug')
            ->with(Mockery::type('string'), Mockery::type('array'))
            ->once();

        $this->repo->shouldReceive('formatTag')
            ->with($object->id)
            ->once()
            ->andReturn('tag');

        $reflection = new ReflectionClass($this->repo);
        $instance = $reflection->getProperty('object');
        $instance->setAccessible(true);
        $instance->setValue($this->repo, $object);
        $objects = $reflection->getProperty('instances');
        $objects->setAccessible(true);
        $objects->setValue($this->repo, $instances);

        expect($this->repo->delete())->true();
        expect($objects->getValue($this->repo))->hasntKey('tag');
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

        $this->repo->shouldReceive('getModelClass')
            ->withNoArgs()
            ->once()
            ->andReturn($model);

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

    public function testBuildTagSuffix()
    {
        $prefix = 'appendix';
        $oid = 19;
        $suffix = 'test';

        $reflection = new ReflectionClass($this->repo);
        $method = $reflection->getMethod('buildTag');
        $method->setAccessible(true);

        expect($method->invoke(null, $prefix, $oid, $suffix))->equals($prefix . '-' . $oid . '-' . $suffix);
    }

    public function testToJson()
    {
        $object = Mockery::mock('stdClass');
        $object->shouldReceive('toJson')
            ->once()
            ->andReturn(false);

        $reflection = new ReflectionClass($this->repo);
        $property = $reflection->getProperty('object');
        $property->setAccessible(true);
        $property->setValue($this->repo, $object);

        expect($this->repo->toJson())->false();
    }

    public function testToJsonOption()
    {
        $option = 45;

        $object = Mockery::mock('stdClass');
        $object->shouldReceive('toJson')
            ->with($option)
            ->once()
            ->andReturn(false);

        $reflection = new ReflectionClass($this->repo);
        $property = $reflection->getProperty('object');
        $property->setAccessible(true);
        $property->setValue($this->repo, $object);

        expect($this->repo->toJson($option))->false();
    }

    public function testJsonSerialize()
    {
        $object = Mockery::mock('stdClass');
        $object->shouldReceive('jsonSerialize')
            ->once()
            ->andReturn(false);

        $reflection = new ReflectionClass($this->repo);
        $property = $reflection->getProperty('object');
        $property->setAccessible(true);
        $property->setValue($this->repo, $object);

        expect($this->repo->jsonSerialize())->false();
    }

    public function testToArray()
    {
        $object = Mockery::mock('stdClass');
        $object->shouldReceive('toArray')
            ->once()
            ->andReturn(false);

        $reflection = new ReflectionClass($this->repo);
        $property = $reflection->getProperty('object');
        $property->setAccessible(true);
        $property->setValue($this->repo, $object);

        expect($this->repo->toArray())->false();
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
        $model = 'C4tech\Support\Model';

        Config::shouldReceive('get')
            ->with($model, $model)
            ->twice()
            ->andReturn($model);

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
