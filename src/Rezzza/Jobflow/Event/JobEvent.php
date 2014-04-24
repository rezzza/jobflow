<?php

namespace Rezzza\Jobflow\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * @author Timothée Barray <tim@amicalement-web.net>
 */
class JobEvent extends Event
{
    private $job;
    private $execution;

    /**
     * @param \Rezzza\Jobflow\Job $job
     * @param \Rezzza\Jobflow\Scheduler\ExecutionContext $execution
     */
    public function __construct($job, $execution)
    {
        $this->job = $job;
        $this->execution = $execution;
    }

    public function getJob()
    {
        return $this->job;
    }

    public function getExecutionContext()
    {
        return $this->execution;
    }
}
