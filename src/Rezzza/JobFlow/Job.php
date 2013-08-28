<?php

namespace Rezzza\JobFlow;

/**
 * Job can aggregate others jobs.
 *
 * @author Timothée Barray <tim@amicalement-web.net>
 */
class Job implements \IteratorAggregate, JobInterface
{
    /**
     * @var JobInterface[]
     */
    protected $children = array();

    /**
     * @var string
     */
    protected $name;

    /**
     * Contains Job Service and IoDescriptor. Helps to build Job easier
     *
     * @var ResolvedJob
     */
    protected $resolved;

    /**
     * @var array
     */
    protected $options;

    /**
     * When a job is processed by JobScheduler we need to ensure it will not change
     *
     * @var boolean
     */
    protected $locked = false;

    /**
     * @var string $name
     * @var ResolvedJob $resolved
     * @var array $options
     */
    public function __construct($name, ResolvedJob $resolved, array $options = array())
    {
        $this->name = $name;
        $this->resolved = $resolved;
        $this->options = $options;
    }

    /**
     * @param JobInterface $child
     */
    public function add(JobInterface $child)
    {
        if ($this->isLocked()) {
            throw new \RuntimeException('Cannot add child on job locked');
        }

        $this->children[$child->getName()] = $child;
    }

    /**
     * @return JobInterface
     */
    public function get($name)
    {
        if (!array_key_exists($name, $this->children)) {
            throw new \LogicException(sprintf('No child with name : "%s" in job "%s"', $name, $this->name));
        }

        return $this->children[$name];
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @return ResolvedJob
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
     * @return JobInterface[]
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * @param boolean $locked
     */
    public function setLocked($locked)
    {
        $this->locked = $locked;
    }

    /**
     * @return boolean
     */
    public function isLocked()
    {
        return $this->locked;
    }

    public function execute($input, $context)
    {
        if ($this->isLocked()) {
            throw new \RuntimeException('Cannot execute job not locked');
        }

        return $this->getResolved()->execute($input, $context);
    }

    /**
     * Returns the iterator for this job.
     *
     * @return \RecursiveArrayIterator
     */
    public function getIterator()
    {
        return new \RecursiveArrayIterator($this->children);
    }

    public function __toString()
    {
        return $this->getName();
    }
}