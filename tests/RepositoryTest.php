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
            ->with('Binding cache flusher for model.', ['model' => $model])
            ->once();

        /* Test Closure
        Mockery::on(function ($method) use ($model) {
                $method($model);
            })
        Log::shouldReceive('debug')
            ->with('Flushing model cache', ['tag' => $tag])
            ->once();
        Cache::shouldReceive('tags->flush')
            ->once();
        */

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

        $this->repo->shouldReceive('ensureModel')
            ->once()
            ->andReturn($model)
            ->getMock();
        $this->repo->shouldReceive('pushCache')
            ->once();

        expect_not($this->repo->__construct());
        expect($this->repo->getModel())->equals($model);
    }
}
