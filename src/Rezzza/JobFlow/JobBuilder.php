<?php

namespace Rezzza\JobFlow;

use Rezzza\JobFlow\Io\IoDescriptor;

/**
 * Make Job creation easier, better, stronger
 *
 * @author Timothée Barray <tim@amicalement-web.net>
 */
class JobBuilder
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var array
     */
    protected $children = array();

    /**
     * @var array
     */
    protected $unresolvedChildren = array();

    /**
     * @var JobFactory
     */
    protected $jobFactory;

    /**
     * @var ResolvedJob
     */
    protected $resolved;

    protected $io;

    /**
     * @var array
     */
    protected $options = array();

    /**
     * @param string $name
     * @param JobFactory $jobFactory
     * @param array $options
     */
    public function __construct($name, JobFactory $jobFactory, array $options = array())
    {
        $this->name = $name;
        //$this->io = $io;
        $this->jobFactory = $jobFactory;
        $this->options = $options;
    }

    /**
     * Add sub job to a job
     *
     * @param string $child Name of the child
     * @param mixed $type The JobTypeInterface or the alias of the job type registered as a service
     * @param IoDescriptor $io To connect jobs together
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
    public function create($name, $type, IoDescriptor $io = null, array $options = array())
    {
        return $this->jobFactory->createNamedBuilder($name, $type, $io, $options);
    }

    /**
     * Create the job with all children configure
     *
     * @return Job
     */
    public function getJob()
    {
        $this->resolveChildren();

        $job = new Job($this->name, $this->resolved, $this->getOptions());

        foreach ($this->children as $child) {
            $job->add($child->getJob());
        }

        return $job;
    }

    /**
     *  @param ResolvedJob $resolved
     */
    public function setResolved(ResolvedJob $resolved)
    {
        $this->resolved = $resolved;
    }

    /**
     *  @return ResolvedJob
     */
    public function getResolved()
    {
        return $this->resolved;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     *  @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param string $name
     * @param mixed $default
     *
     * @return mixed
     */
    public function getOption($name, $default = null)
    {
        return array_key_exists($name, $this->options) ? $this->options[$name] : $default;
    }

    /**
     * For each child added, we create a new JobBuilder around it to make fully configurable each sub job
     */
    private function resolveChildren()
    {
        foreach ($this->unresolvedChildren as $name => $info) {
            $this->children[$name] = $this->create($name, $info['type'], $this->getOption('io'), $info['options']);
        }

        $this->unresolvedChildren = array();
    }
}