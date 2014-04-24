<?php

namespace Rezzza\Jobflow\Processor;

use Rezzza\Jobflow\Scheduler\ExecutionContext;

interface JobProcessor
{
    /**
     * @return void
     */
    public function execute(ExecutionContext $execution);
}
