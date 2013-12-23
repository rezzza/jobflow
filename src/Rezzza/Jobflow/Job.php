<?php

namespace Rezzza\Jobflow;

use Rezzza\Jobflow\Event\JobEvent;
use Rezzza\Jobflow\Event\JobEvents;
use Rezzza\Jobflow\Scheduler\ExecutionContext;
use Rezzza\Jobflow\Processor\ProcessorConfig;

/**
 * @author Timothée Barray <tim@amicalement-web.net>
 */
class Job implements \IteratorAggregate, JobInterface
{
    /**
     * @var JobConfig
     */
    private $config;

    /**
     * @var JobInterface
     */
    private $parent;

    /**
     * @var JobInterface[]
     */
    protected $children = array();

    /**
     * @var JobConfig $config
     */
    public function __construct(JobConfig $config)
    {
        $this->config = $config;
    }

    public function setParent(JobInterface $parent = null)
    {
        if (null !== $parent && '' === $this->config->getName()) {
            throw new \LogicException('A job with an empty name cannot have a parent job.');
        }

        $this->parent = $parent;

        return $this;
    }

    /**
     * @var ExecutionContext $execution
     */
    public function execute(ExecutionContext $execution)
    {
        $options = $this->getExecOptions();

        // We inject execution here to be able to use it hen.
        $options['execution'] = $execution;

        $options = $this->getResolved()->execJob($this->getConfig(), $options);

        $this->getConfig()->setResolvedExecOptions($options);

        $dispatcher = $this->config->getEventDispatcher();

        if ($dispatcher->hasListeners(JobEvents::PRE_EXECUTE)) {
            $dispatcher->dispatch(JobEvents::PRE_EXECUTE, new JobEvent($this));
        }

        $processorConfig = $this->config->getProcessorConfig();

        if ($processorConfig instanceof ProcessorConfig) {
            $factory = new \Rezzza\Jobflow\Processor\ProcessorFactory;
            $factory
                ->create($processorConfig, $this->config->getMetadataAccessor())
                ->execute($execution)
            ;
        } elseif (is_callable($processorConfig)) {
            call_user_func_array(
                $processorConfig,
                [$execution]
            );
        } else {
            throw new \InvalidArgumentException('processor should be a ProcessorConfig or a callable');
        }

        if ($dispatcher->hasListeners(JobEvents::POST_EXECUTE)) {
            $dispatcher->dispatch(JobEvents::POST_EXECUTE, new JobEvent($this));
        }
    }

    /**
     * @param JobInterface $child
     */
    public function add(JobInterface $child)
    {
        $child->setParent($this);

        $this->children[$child->getName()] = $child;
    }

    /**
     * @param $name
     *
     * @return JobInterface
     */
    public function get($name)
    {
        if (!array_key_exists($name, $this->children)) {
            throw new \LogicException(sprintf('No child with name : "%s" in job "%s"', $name, $this->getName()));
        }

        return $this->children[$name];
    }

    /**
     * @return JobConfig
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @return array
     */
    public function getInitOptions()
    {
        return $this->config->getInitOptions();
    }

    /**
     * @return array
     */
    public function getExecOptions()
    {
        return $this->config->getExecOptions();
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->config->getOptions();
    }

    /**
     * @return array
     */
    public function getOption($name, $default = null)
    {
        return $this->config->getOption($name, $default);
    }

    /**
     * @return ResolvedJob
     */
    public function getResolved()
    {
        return $this->config->getResolved();
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->config->getName();
    }

    /**
     * @return JobInterface[]
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * @return JobInterface
     */
    public function getParent()
    {
        return $this->parent;
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

    /**
     * @return string
     */
    public function getFullName()
    {
        return $this->getParent()->getName().'.'.$this->getName();
    }

    public function getRequeue()
    {
        return $this->config->getRequeue();
    }

    public function __toString()
    {
        return $this->getName();
    }
}
