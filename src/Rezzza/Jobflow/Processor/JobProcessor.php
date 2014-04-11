<?php

namespace Rezzza\Jobflow\Processor;

use Rezzza\Jobflow\Scheduler\ExecutionContext;

interface JobProcessor
{
    public function execute(ExecutionContext $execution);
}
