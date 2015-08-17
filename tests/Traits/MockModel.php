<?php namespace C4tech\Test\Support\Traits;

use C4tech\Support\Model;
use C4tech\Support\Traits\JsonableColumn;
use C4tech\Support\Traits\JsonableApiModel;

class MockModel extends Model
{
    use JsonableApiModel, JsonableColumn;
}
