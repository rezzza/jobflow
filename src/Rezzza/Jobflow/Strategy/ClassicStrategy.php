<?php

namespace Rezzza\Jobflow\Strategy;

use Rezzza\Jobflow\Extension\Pipe\Pipe;
use Rezzza\Jobflow\JobMessage;
use Rezzza\Jobflow\Scheduler\Jobflow;

class ClassicStrategy implements MessageStrategyInterface
{
    public function handle(Jobflow $jobflow, JobMessage $msg)
    {
        $current = $msg->context->getCurrent();

        // Move graph to the current value
        $jobflow->getJobGraph()->move($current);

        // Gets the current job
        $child = $jobflow->getJob()->get($current);

        if ($msg->pipe instanceof Pipe) {
            $jobflow->forwardPipeMessage($msg, $jobflow->getJobGraph());
            
            // Reset pipe as we already ran through above
            $msg->pipe = array();
        } 

        if (true === $child->getRequeue()) {
            $msg->context->tick();

            if (!$msg->context->isFinished()) {
                $origin = $msg->context->getOrigin();
                $jobflow->getJobGraph()->move($origin);

                $msg->context->addStep($current);
                $msg->context->setCurrent($origin);
            } else {
                $msg = null;
            }
        } elseif (!$jobflow->getJobGraph()->hasNextJob()) {
            $msg = null;
        } else {
            $msg->context->updateToNextJob($jobflow->getJobGraph());
        }

        if (null !== $msg) {
            $jobflow->addMessage($msg);
        }
    }
}