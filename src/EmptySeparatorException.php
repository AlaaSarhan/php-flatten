<?php

namespace Sarhan\Flatten;

class EmptySeparatorException extends \Exception
{
    public function __construct()
    {
        parent::__construct('Cannot unflatten with an empty separator');
    }
}
