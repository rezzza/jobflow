<?php

namespace Rezzza\Jobflow\Strategy;

use Rezzza\Jobflow\Extension\Pipe\Pipe;
use Rezzza\Jobflow\JobMessage;
use Rezzza\Jobflow\Scheduler\Jobflow;
use Rezzza\Jobflow\Scheduler\JobExecutionContext;

class ClassicStrategy implements MessageStrategyInterface
{
    public function handle($execution, $messageFactory)
    {
        // Gets the current job
        $child = $execution->currentChild();
        $msgs = $execution->createPipeMsgs($messageFactory);

        if (true === $child->getRequeue()) {
            $execution->tick();

            if (!$execution->isFinished()) {
                // Create following msg by reset position msg
                $msgs[] = $execution->createNextMsg($messageFactory);
            }
        } elseif ($execution->hasNextJob()) {
            // Create following msg by updating to next step
            $msgs[] = $execution->createNextMsg($messageFactory);
        }

        return $msgs;
    }
}