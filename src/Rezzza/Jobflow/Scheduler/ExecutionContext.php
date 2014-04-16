<?php

namespace Rezzza\Jobflow\Scheduler;

use Rezzza\Jobflow\Io;
use Rezzza\Jobflow\JobInterface;
use Rezzza\Jobflow\JobContext;
use Rezzza\Jobflow\JobMessage;
use Rezzza\Jobflow\JobMessageFactory;
use Rezzza\Jobflow\JobPayload;
use Rezzza\Jobflow\JobData;
use Rezzza\Jobflow\Metadata\MetadataAccessor;
use Rezzza\Jobflow\Extension\Pipe\PipeData;

/**
 * Wraps job execution around current context
 */
class ExecutionContext
{
    protected $job;

    protected $jobGraph;

    protected $jobContext;

    protected $input;

    protected $output;

    public function __construct(JobInterface $job, JobGraph $jobGraph)
    {
        $this->job = $job;
        $this->jobGraph = $jobGraph;
    }

    public function execute(JobContext $context, JobPayload $input, JobMessageFactory $msgFactory)
    {
        $this->initContext($context);
        $this->input = $input;
        $this->output = new JobPayload;

        $child = $this
            ->currentChild()
            ->execute($this)
        ;

        return $msgFactory->createMsg($this->jobContext, $this->output);
    }

    public function read()
    {
        return $this->input;
    }

    public function write($result, $metadata = null)
    {
        $this->output->store(new JobData($result, $metadata));
    }

    public function valid()
    {
        $this->output->filter();

        if (count($this->output) <= 0) {
            $this->terminate();
        }
    }

    public function rewindData()
    {
        $this->output = $this->input;
    }

    public function currentChild()
    {
        return $this->jobContext->currentChild($this->job);
    }

    public function terminate()
    {
        $this->jobContext->terminate();
    }

    public function hasNoTotal()
    {
        return null === $this->jobContext->getOption('total');
    }

    public function changeTotal($total)
    {
        $max = $this->jobContext->getOption('max');

        if (null !== $max && $max < $total) {
            $total = $max;
        }

        $this->jobContext->setOption('total', $total);
    }

    public function logState($logger)
    {
        if (!$logger) {
            return;
        }

        $this->jobContext->logState($logger);
    }

    public function getOffset()
    {
        $offset = $this->jobContext->getOption('offset');

        // When job need to start from an defined offset
        // (Typically when first lines are not suit for reading)
        if ($this->getJobOption('offset', 0) > $offset) {
            $offset = $this->jobContext->getOption('offset');
            $this->jobContext->setOption('offset', $offset);
        }

        return $offset;
    }

    public function getLimit()
    {
        return $this->jobContext->getOption('limit');
    }

    public function getContextOptions()
    {
        return $this->jobContext->getOptions();
    }

    public function getJobOption($name, $default = null)
    {
        return $this->currentChild()->getOption($name, $default);
    }

    public function getIo()
    {
        return $this->jobContext->io;
    }

    public function getContextMetadata()
    {
        return $this->jobContext->metadata;
    }

    private function initContext(JobContext $context)
    {
        $this->jobContext = $context;
        $context->initGraph($this->jobGraph);
    }
}
