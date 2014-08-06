<?php

namespace Rezzza\Jobflow\Strategy;

use Rezzza\Jobflow\JobMessageFactory;
use Rezzza\Jobflow\JobMessage;
use Rezzza\Jobflow\NoMoreMessageException;
use Rezzza\Jobflow\Scheduler\JobGraph;

class ClassicStrategy implements MessageStrategyInterface
{
    private $jobFactory;

    private $ctxFactory;

    private $msgFactory;

    /**
     * @param \Rezzza\Jobflow\JobFactory $jobFactory
     * @param \Rezzza\Jobflow\JobContextFactory $ctxFactory
     * @param JobMessageFactory $msgFactory
     */
    public function __construct($jobFactory, $ctxFactory, $msgFactory)
    {
        $this->jobFactory = $jobFactory;
        $this->ctxFactory = $ctxFactory;
        $this->msgFactory = $msgFactory;
    }

    public function handle(JobMessage $msg)
    {
        $job = $msg->recoverJob($this->jobFactory);
        $graph = new JobGraph($job);
        $msg->initGraph($graph);
        $child = $msg->currentChild($job);
        $msgs = $msg->createPipeMsgs($job, $graph, $this->ctxFactory, $this->msgFactory);
        $forceRequeue = $child->getRequeue();

        if (true === $forceRequeue || $msg->shouldContinue($graph)) {
            try {
                $msgs[] = $msg->createNextMsg($graph, $this->msgFactory, $forceRequeue);
            } catch (NoMoreMessageException $e) {
                // We could log it ?
            }
        }

        return $msgs;
    }
}
