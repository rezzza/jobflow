<?php

namespace Rezzza\JobFlow;

use Rezzza\JobFlow\Io\IoDescriptor;

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
        $this->children[$child] = null; // to keep order
        $this->unresolvedChildren[$child] = array(
            'type' => $type,
            'options' => $options
        );

        return $this;
    }

    /**
     * Create new JobBuilder
     *
     * @param string $name
     * @param mixed $type The JobTypeInterface or the alias of the job type registered as a service
     * @param IoDescriptor $io To connect jobs together
     * @param array $options
     *
     * @return JobBuilder
     */
    public function create($name, $type, array $options = array())
    {
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

    /**
     * For each child added, we create a new JobBuilder around it to make fully configurable each sub job
     */
    private function resolveChildren()
    {
        foreach ($this->unresolvedChildren as $name => $info) {
            $this->children[$name] = $this->create($name, $info['type'], $info['options']);
        }

        $this->unresolvedChildren = array();
    }
}