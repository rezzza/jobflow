<?php

namespace Rezzza\JobFlow;

use Rezzza\JobFlow\Scheduler\JobGraph;

/**
 * @author Timothée Barray <tim@amicalement-web.net>
 */
class JobContext implements JobContextInterface
{
    /**
     * The job id to execute
     *
     * @var string
     */
    public $jobId;

    /**
     * Current child job in execution
     */
    public $current;

    /**
     * Steps already executed
     */
    public $steps = array();

    /**
     * @var array
     */
    public $options = array();
    
    public function __construct($jobId)
    {
        $this->jobId = $jobId;
        $this->initOptions();
    }

    public function updateToNextJob(JobGraph $graph)
    {
        // We stock we executed this job
        $this->addStep($this->current);

        if ($graph->hasNextJob()) {
            $nextJob = $graph->getNextJob();
        } else {
            $this->options['offset'] += $this->options['limit'];
            $nextJob = null;

            if (!$this->isFinished()) {
                // Check if we should reloop
                $nextJob = $graph->getJob(0);
            }
        }

        $this->current = $nextJob;
    }

    public function addStep($step)
    {
        $this->steps[] = $step;
    }

    public function getPrevious()
    {
        return end($this->steps);
    }

    public function hasNextJob()
    {
        return null !== $this->current;
    }

    public function isFinished()
    {
        return $this->options['total'] <= $this->options['offset'];
    }

    public function isStarting()
    {
        return count($this->steps) === 0;
    }

    public function getMessageName()
    {
        return sprintf('%s.%s', $this->jobId, $this->current);
    }

    public function initOptions()
    {
        $this->options = array(
            'total' => null,
            'offset' => 0,
            'limit' => 10
        );
    }
}