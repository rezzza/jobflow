<?php

namespace Rezzza\JobFlow;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Rezzza\JobFlow\Scheduler\JobGraph;

/**
 * Keeps state for the job execution
 *
 * @author Timothée Barray <tim@amicalement-web.net>
 */
class JobContext implements JobContextInterface
{
    /**
     * The job we run
     *
     * @var string
     */
    private $jobId;

    /**
     * Current job name in execution
     *
     * @var string
     */
    private $current;

    /**
     * Steps already executed
     *
     * @var array
     */
    private $steps = array();

    /**
     * @var array
     */
    private $options = array();
    
    public function __construct($jobId, array $options = array())
    {
        $this->jobId = $jobId;

        $resolver = new OptionsResolver();
        $this->setDefaultOptions($resolver);
        $this->options = $resolver->resolve($options);
    }

    /**
     * Moves the execution graph to the next job
     *
     * @param JobGraph $graph
     */
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
                $nextJob = $graph->getJob(0);
            }
        }

        $this->current = $nextJob;
    }

    public function getJobId()
    {
        return $this->jobId;
    }

    /**
     * @param string $current
     */
    public function setCurrent($current)
    {
        $this->current = $current;

        return $this;
    }

    /**
     * @return string
     */
    public function getCurrent()
    {
        return $this->current;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    public function setOptions(array $options)
    {
        $this->options = $options;
    }

    public function getOption($name, $default = null)
    {
        return array_key_exists($name, $this->options) ? $this->options[$name] : $default;
    }

    /**
     * @param mixed $key
     * @param mixed $value
     *
     * @return JobContext
     */
    public function setOption($key, $value)
    {
        $this->options[$key] = $value;

        return $this;
    }

    /**
     * Gets the previous job executed
     *
     * @return string
     */
    public function getPrevious()
    {
        return end($this->steps);
    }

    /**
     * Checks we need to requeue job again
     *
     * @return boolean
     */
    public function isFinished()
    {
        return $this->options['total'] <= $this->options['offset'];
    }

    /**
     * Checks if JobContext has already traveled
     *
     * @return boolean
     */
    public function isStarting()
    {
        return count($this->steps) === 0;
    }

    /**
     * @return string
     */
    public function getMessageName()
    {
        return sprintf('%s.%s', $this->jobId, $this->current);
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'offset' => 0,
            'limit' => 50,
            'total' => null
        ));
    }

    /**
     * Adds step to keep trace
     */
    protected function addStep($step)
    {
        $this->steps[] = $step;
    }
}