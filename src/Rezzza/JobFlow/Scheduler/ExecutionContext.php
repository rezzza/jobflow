<?php

namespace Rezzza\JobFlow\Scheduler;

use Psr\Log\LoggerAwareTrait;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Rezzza\JobFlow\JobInterface;
use Rezzza\JobFlow\JobMessage;

/**
 * Wrap and contextualize execution of job
 *
 * @author Timothée Barray <tim@amicalement-web.net>
 */
class ExecutionContext
{
    use LoggerAwareTrait;

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
     * Representation of the navigation through the jobs
     *
     * @var RecursiveArrayIterator
     */
    public $graph;

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
    public function __construct(JobMessage $msg, JobGraph $graph)
    {
        $this->graph = $graph;
        $this->msg = $msg;
        $this->globalContext = $this->msg->context;
        $this->globalContext->moveToCurrent($this->graph);
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

        if ($this->logger) {
            $this->logger->debug('Try to execute '.$this->job->getName());
        }
        
        return $this->job->execute($this);
    }

    /**
     * Checks if we starts the graph
     *
     * @return boolean
     */
    public function isFirstStep()
    {
        return $this->graph->key() === 0;
    }

    /**
     * Checks if we ends the graph
     *
     * @return boolean
     */
    public function isLastStep()
    {
        return $this->graph->key() === (count($this->graph) - 1);
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


    public function getLogger()
    {
        return $this->logger;
    }

    public function setGlobalOption($key, $value)
    {
        $this->globalContext->setOption($key, $value);
    }

    public function getGlobalOption($name)
    {
        return $this->globalContext->getOption($name);
    }

    public function getJobOption($name)
    {
        return $this->job->getOption($name);
    }
}