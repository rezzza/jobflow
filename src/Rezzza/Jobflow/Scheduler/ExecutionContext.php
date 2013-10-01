<?php

namespace Rezzza\Jobflow\Scheduler;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Rezzza\Jobflow\JobInterface;
use Rezzza\Jobflow\JobMessage;

/**
 * Wrap and contextualize execution of job
 *
 * @author Timothée Barray <tim@amicalement-web.net>
 */
class ExecutionContext
{
    /**
     * Current msg
     *
     * @var JobMessage
     */
    public $msg;

    /**
     * Global Context moved from message to message
     *
     * @var JobContext
     */
    public $globalContext;

    /**
     * Current job in execution
     *
     * @var JobInterface
     */
    public $job;

    /**
     * @param JobMessage $msg
     * @param JobGraph $graph
     */
    public function __construct(JobMessage $msg)
    {
        $this->msg = $msg;
        $this->globalContext = $this->msg->context;
    }

    /**
     * Run execute on a job for the current msg.
     * It will determine himself which child need to be execute
     *
     * @param JobInterface $job
     */
    public function executeJob(JobInterface $parent)
    {
        if (null === $this->getCurrentJob()) {
            // No more Job to run. debug
            return 0;
        }
        
        $this->job = $parent->get($this->getCurrentJob());
        
        return $this->job->execute($this);
    }

    /**
     * Get name of the child job in execution
     * 
     * @return string
     */
    public function getCurrentJob()
    {
        return $this->globalContext->getCurrent();
    }

    /**
     * @return string
     */
    public function getJobId()
    {
        return $this->globalContext->jobId;
    }

    public function setGlobalOption($key, $value)
    {
        $this->globalContext->setOption($key, $value);
    }

    public function getGlobalOption($name)
    {
        return $this->globalContext->getOption($name);
    }

    public function getJobOption($name, $default = null)
    {
        return $this->job->getOption($name, $default);
    }

    public function getLogger()
    {
        return $this->job->getLogger();
    }
}