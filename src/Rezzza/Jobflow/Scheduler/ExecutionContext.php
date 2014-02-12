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

    protected $pipe;

    public function __construct(JobInterface $job, JobGraph $jobGraph)
    {
        $this->job = $job;
        $this->jobGraph = $jobGraph;
        $this->output = new JobPayload();

        $this->initPipe();
    }

    public function execute(JobMessage $msg, JobMessageFactory $msgFactory)
    {
        $child = $this
            ->start($msg)
            ->currentChild()
        ;

        $child->execute($this);

        return $msgFactory->createMsg($this->jobContext, $this->output);
    }

    public function tick()
    {
        $this->jobContext->tick();
    }

    public function initContext(JobContext $context)
    {
        $this->jobContext = $context;
        $context->initGraph($this->jobGraph);
    }

    public function initInput(JobPayload $payload)
    {
        $this->input = $payload;
    }

    public function initOutput(JobPayload $payload)
    {
        $this->output = $payload;
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

    public function start(JobMessage $msg)
    {
        $msg->initExecutionContext($this);
        $msg->initExecutionInput($this);
        $this->output = new JobPayload();

        return $this;
    }

    public function end(JobMessage $msg)
    {
        $msg->initExecutionContext($this);
        $msg->initExecutionOutput($this);

        return $this;
    }

    public function createPipeMsgs($msgFactory)
    {
        $stdout = null;
        $msgs = [];

        if ($this->getIo()) {
            $stdout = $this->getIo()->getStdout();
        }

        foreach ($this->output as $data) {
            if ($data->getValue() instanceof Io\Input) {
                $io = new Io\IoDescriptor($data->getValue(), $stdout);

                $context = new JobContext(
                    $this->job->getName(),
                    $io,
                    $this->jobGraph->getNextJob(),
                    $this->job->getConfig()->getOption('context', []),
                    $this->job->getOptions(),
                    $this->jobContext->transport,
                    $data->getMetadata()
                );

                $msgs[] = $msgFactory->createMsg($context, new JobPayload);
            }
        }

        return $msgs;
    }

    public function createNextMsg($msgFactory)
    {
        $next = $this->jobGraph->getNextJob();

        if ($next) {
            $this->jobContext->moveTo($next);
        } else {
            $this->jobContext->reset();
        }

        return $msgFactory->createMsg($this->jobContext, $this->output);
    }

    public function createResetMsg($msgFactory)
    {
        $this->jobContext->reset();

        return $msgFactory->createMsg($this->jobContext, new JobPayload());
    }

    public function rewindData()
    {
        $this->output = $this->input;
    }

    public function currentChild()
    {
        return $this->jobContext->currentChild($this->job);
    }

    public function hasNextJob()
    {
        return $this->jobGraph->hasNextJob();
    }

    public function isFinished()
    {
        return (is_integer($this->getContextOption('total')) && $this->getContextOption('total') <= $this->getContextOption('offset'));
    }

    public function isTerminated()
    {
        return true === $this->jobContext->terminated;
    }

    public function shouldContinue()
    {
        return !$this->isTerminated() && $this->hasNextJob();
    }

    public function terminate()
    {
        $this->jobContext->terminated = true;
    }

    public function logState($logger)
    {
        if (!$logger) {
            return;
        }

        $this->jobContext->logState($logger);
    }

    public function setContextOption($key, $value)
    {
        return $this->jobContext->setOption($key, $value);
    }

    public function getContextOption($name)
    {
        return $this->jobContext->getOption($name);
    }

    public function getContextOptions()
    {
        return $this->jobContext->getOptions();
    }

    public function getJobOption($name, $default = null)
    {
        return $this->currentChild()->getOption($name, $default);
    }

    public function getLogger()
    {
        return $this->currentChild()->getConfig()->getAttribute('logger');
    }

    public function getIo()
    {
        return $this->jobContext->io;
    }

    public function getContextMetadata()
    {
        return $this->jobContext->metadata;
    }

    protected function initPipe()
    {
        $this->pipe = new Io\InputAggregator;
    }
}
