<?php

namespace Rezzza\Jobflow\Strategy;

use Rezzza\Jobflow\JobMessageFactory;
use Rezzza\Jobflow\JobMessage;
use Rezzza\Jobflow\Job;
use Rezzza\Jobflow\Scheduler\JobGraph;

class ClassicStrategy implements MessageStrategyInterface
{
    private $jobFactory;

    private $ctxFactory;

    private $msgFactory;

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
        $msgs = $msg->createPipeMsgs($job, $graph, $this->ctxFactory);

        if (true === $child->getRequeue() || $msg->isTerminated()) {
            // Create following msg by reset position msg to the origin if needed
            // If we go through all data, will return null
            $resetMsg = $msg->createResetMsg($this->msgFactory);

            if (null !== $resetMsg) {
                $msgs[] = $resetMsg;
            }
        } elseif ($msg->shouldContinue($graph)) {
            // Create following msg by updating to next step
            $msgs[] = $msg->createNextMsg($graph, $this->msgFactory);
        }

        return $msgs;
    }
}
