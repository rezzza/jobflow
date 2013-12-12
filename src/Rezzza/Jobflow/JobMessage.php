<?php

namespace Rezzza\Jobflow;

use Rezzza\Jobflow\Scheduler\ExecutionContext;

/**
 * State representation between each job loop execution.
 * We should be able to pick up where we left job execution thanks to this object.
 */
class JobMessage
{
    protected $context;

    protected $payload;

    public $pipe = array();

    protected $ended = false;

    public function __construct(JobContext $context, JobPayload $payload)
    {
        $this->context = $context;
        $this->payload = $payload;
    }

    public function __clone()
    {
        $this->context = clone $this->context;
    }

    public function isEnded()
    {
        return true === $this->ended;
    }

    public function createStartedJobExecution($jobFactory)
    {
        $execution = new ExecutionContext(
            $jobFactory->create($this->context->jobId, $this->context->jobOptions)
        );

        $execution->start($this);

        return $execution;
    }

    public function createEndedJobExecution($jobFactory)
    {
        $execution = new ExecutionContext(
            $jobFactory->create($this->context->jobId, $this->context->jobOptions)
        );

        $execution->end($this);
echo $execution->currentChild(); exit;

        return $execution;
    }

    public function initExecutionContext($executionContext)
    {
        $executionContext->initContext($this->context);
    }

    public function initExecutionInput($execution)
    {
        $execution->initInput($this->payload);
    }

    public function initExecutionOutput($execution)
    {
        $execution->initOutput($this->payload);
    }

    public function getUniqName()
    {
        return $this->context->getMessageName().uniqid();
    }

    public function changeData($payload)
    {
        $this->payload = $payload;

        return $this;
    }

    public function logState($logger)
    {
        if (!$logger) {
            return;
        }

        if (null === $this->context->getCurrent()) {
            $step = 'starting';
        } else {
            $step = 'step '.$this->context->getCurrent();
        }

        $logger->info(sprintf(
            'Add new message for job [%s] : %s',
            $this->context->jobId,
            $step
        ));
    }
}