<?php

namespace Rezzza\Jobflow;

/**
 * Make Job creation easier, better, stronger
 *
 * @author Timothée Barray <tim@amicalement-web.net>
 */
class JobBuilder extends JobConfig
{
    /**
     * @var array
     */
    protected $children = array();

    /**
     * @var array
     */
    protected $unresolvedChildren = array();

    /**
     * @param string $name
     * @param JobFactory $jobFactory
     * @param array $options
     */
    public function __construct($name, JobFactory $jobFactory, array $options = array())
    {
        parent::__construct($name, $options);
        $this->setJobFactory($jobFactory);
    }

    /**
     * Add sub job to a job
     *
     * @param string $child Name of the child
     * @param mixed $type The JobTypeInterface or the alias of the job type registered as a service
     * @param array $options
     *
     * @return JobBuilder
     */
    public function add($child, $type, array $options = array())
    {
        if (!is_string($child) && !is_int($child)) {
            throw new \InvalidArgumentException(sprintf('child name should be string or, integer'));
        }

        if (null !== $type && !is_string($type) && !$type instanceof JobTypeinterface) {
            throw new \InvalidArgumentException('type should be string or JobTypeinterface');
        }

        $this->children[$child] = null; // to keep order
        $this->unresolvedChildren[$child] = array(
            'type' => $type,
            'options' => $options
        );

        return $this;
    }

    /**
     * @param string $name
     */
    public function has($name)
    {
        return isset($this->unresolvedChildren[$name]) 
            || isset($this->children[$name]);
    }

    /**
     * Create new JobBuilder
     *
     * @param string $name
     * @param mixed $type The JobTypeInterface or the alias of the job type registered as a service
     * @param array $options
     *
     * @return JobBuilder
     */
    public function create($name, $type = null, array $options = array())
    {
        if (null === $type) {
            $type = 'job';
        }

        return $this->jobFactory->createNamedBuilder($name, $type, null, $options);
    }

    /**
     * Create the job with all children configure
     *
     * @return Job
     */
    public function getJob()
    {
        $this->resolveChildren();

        $job = new Job($this->getJobConfig());

        foreach ($this->children as $child) {
            $job->add($child->getJob());
        }

        return $job;
    }

    public function hasUnresolvedChildren()
    {
        return count($this->unresolvedChildren) > 0;
    }

    /**
     * For each child added, we create a new JobBuilder around it to make fully configurable each sub job
     */
    protected function resolveChildren()
    {
        $childrenKeys = array_keys($this->unresolvedChildren);

        $bounds = array(
            'first' => reset($childrenKeys),
            'last' => end($childrenKeys)
        );

        foreach ($this->unresolvedChildren as $name => $info) {
            // Waiting for better idea, we inject IO to the first and last step
            // In order to make easier ETL usage
            if (in_array($name, $bounds)) {
                $info['options']['io'] = $this->getOption('io');
            }

            $this->children[$name] = $this->create($name, $info['type'], $info['options']);
        }

        $this->unresolvedChildren = array();
    }
}