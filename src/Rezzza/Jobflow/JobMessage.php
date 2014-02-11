<?php

namespace Rezzza\Jobflow;

use Psr\Log\LoggerInterface;
use Rezzza\Jobflow\Scheduler\ExecutionContext;

/**
 * State representation between each job loop execution.
 * We should be able to pick up where we left job execution thanks to this object.
 */
class JobMessage
{
    protected $context;

    protected $payload;

    protected $id;

    public function __construct(JobContext $context, JobPayload $payload)
    {
        $this->context = $context;
        $this->payload = $payload;
        $this->generateId();
    }

    public function __clone()
    {
        $this->context = clone $this->context;
    }

    public function recoverJob(JobFactory $jobFactory)
    {
        return $jobFactory->create($this->context->jobId, $this->context->jobOptions);
    }

    public function initExecutionContext(ExecutionContext $execution)
    {
        $execution->initContext($this->context);
    }

    public function initExecutionInput(ExecutionContext $execution)
    {
        $execution->initInput($this->payload);
    }

    public function initExecutionOutput(ExecutionContext $execution)
    {
        $execution->initOutput($this->payload);
    }

    public function getUniqName()
    {
        return $this->id;
    }

    public function generateId()
    {
        $this->id = $this->context->getMessageName().uniqid();
    }

    public function logState(LoggerInterface $logger)
    {
        $this->context->logState($logger);
    }
}
