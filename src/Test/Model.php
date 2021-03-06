<?php namespace C4tech\Support\Test;

use C4tech\Support\Test\Traits\Relatable;
use C4tech\Support\Test\Traits\Scopeable;

abstract class Model extends Base
{
    /**
     * Consume the Model testing traits
     */
    use Relatable, Scopeable;
}
