<?php

namespace Rezzza\Jobflow\Scheduler;

class ExecutionContextFactory
{
    /**
     * @param \Rezzza\Jobflow\JobInterface $job
     * @param JobGraph $graph
     */
    public function create($job, $graph)
    {
        return new ExecutionContext($job, $graph);
    }
}
