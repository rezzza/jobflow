<?php

namespace Rezzza\Jobflow\Strategy;

use Rezzza\Jobflow\Scheduler\Jobflow;
use Rezzza\Jobflow\JobMessage;

interface MessageStrategyInterface
{
    public function handle(Jobflow $jobflow, $jobExecution, JobMessage $message);
}