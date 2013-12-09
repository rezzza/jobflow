<?php

namespace Rezzza\Jobflow;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Rezzza\Jobflow\Scheduler\JobGraph;

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
     * Step which start this context.
     * At the end of loops, we will requeue to this step
     *
     * @var string
     */
    private $origin;

    /**
     * @var array
     */
    private $options = array();

    public function __construct($jobId, array $options = array(), $current = null)
    {
        $this->jobId = $jobId;
        $this->current = $current;
        $this->initOptions($options);
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
        $nextJob = null;

        if ($graph->hasNextJob()) {
            $nextJob = $graph->getNextJob();
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
        return is_integer($this->options['total']) && $this->options['total'] <= $this->options['offset'];
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
            'total' => null,
            'max' => null
        ));
    }

    public function initOptions(array $options = array())
    {
        $resolver = new OptionsResolver();
        $this->setDefaultOptions($resolver);
        $this->options = $resolver->resolve($options);
    }

    public function tick()
    {
        $this->options['offset'] += $this->options['limit'];
    }

    /**
     * Adds step to keep trace
     */
    public function addStep($step)
    {
        $this->steps[] = $step;
    }

    public function setOrigin($origin)
    {
        $this->origin = $origin;
    }

    public function getOrigin()
    {
        return $this->origin;
    }
}
