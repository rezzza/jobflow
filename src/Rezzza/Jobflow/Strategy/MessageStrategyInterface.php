<?php

namespace Rezzza\Jobflow\Strategy;

use Rezzza\Jobflow\JobMessageFactory;
use Rezzza\Jobflow\Scheduler\ExecutionContext;

interface MessageStrategyInterface
{
    public function handle(ExecutionContext $execution, JobMessageFactory $messageFactory);
}