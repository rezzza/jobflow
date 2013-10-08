<?php

namespace Rezzza\Jobflow\Processor;

use Psr\Log\LoggerAwareTrait;

class JobProcessor
{
    use LoggerAwareTrait;

    protected $processor;

    public function __contruct($processor)
    {
        $this->processor = $processor;
    }

    public function getProcessor()
    {
        return $this->processor;
    }
}