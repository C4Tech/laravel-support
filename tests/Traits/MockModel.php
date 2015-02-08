<?php namespace C4tech\Test\Support\Traits;

use C4tech\Support\Model;
use C4tech\Support\Traits\JsonableColumn;
use C4tech\Support\Traits\Presentable;

class MockModel extends Model
{
    use JsonableColumn;
}
