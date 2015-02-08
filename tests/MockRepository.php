<?php namespace C4tech\Test\Support;

use C4tech\Support\Repository;

class MockRepository extends Repository
{
    protected static $model = 'C4tech\Support\Model';
    protected $something = false;

    public function setTest($value)
    {
        $this->something = $value;
    }
}
