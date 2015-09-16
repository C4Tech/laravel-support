<?php namespace C4tech\Support\Test\Traits;

use Mockery;
use Codeception\Verify;

trait Scopeable
{
    use Modelable;

    /**
     * Get Query Mock
     *
     * Creates a new mocked query object.
     * @return \Mockery\MockInterface
     */
    protected function getQueryMock()
    {
        return Mockery::mock('stdClass')->makePartial();
    }

    /**
     * Verify Scope Where
     * @param  string $method Method name to test
     * @param  string $left   Expected property to test
     * @param  mixed  $right  Expected value to test
     * @param  string $comp   Expected comparison to test
     * @param  mixed  ...     Parameter to pass for setting $right value
     * @return void
     */
    protected function verifyScopeWhere($method, $left, $right, $comp = '=')
    {
        $query = $this->getQueryMock();
        $query->shouldReceive('where')
            ->with($left, $comp, $right)
            ->once()
            ->andReturn(true);

        // Handle passing an argument to the tested method
        $args = func_get_args();
        if (count($args) > 4) {
            expect($this->model->$method($query, $args[4]))->true();
        } else {
            expect($this->model->$method($query, $right))->true();
        }
    }
}
