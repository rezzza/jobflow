<?php

namespace Rezzza\Jobflow\Strategy;

use Rezzza\Jobflow\JobMessageFactory;
use Rezzza\Jobflow\Scheduler\ExecutionContext;

class ClassicStrategy implements MessageStrategyInterface
{
    public function handle(ExecutionContext $execution, JobMessageFactory $messageFactory)
    {
        // Gets the current job
        $child = $execution->currentChild();
        $msgs = $execution->createPipeMsgs($messageFactory);

        if (true === $child->getRequeue()) {
            $execution->tick();

            if (!$execution->isFinished()) {
                // Create following msg by reset position msg to the origin
                $msgs[] = $execution->createResetMsg($messageFactory);
            }
        } elseif ($execution->hasNextJob()) {
            // Create following msg by updating to next step
            $msgs[] = $execution->createNextMsg($messageFactory);
        }

        return $msgs;
    }
}