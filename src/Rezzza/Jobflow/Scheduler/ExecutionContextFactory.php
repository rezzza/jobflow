<?php

namespace Rezzza\Jobflow\Scheduler;

class ExecutionContextFactory
{
    public function create($job, $graph)
    {
        return new ExecutionContext($job, $graph);
    }
}
