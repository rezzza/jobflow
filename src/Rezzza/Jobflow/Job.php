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
        // Will execute buildExec on JobType after execution options
        $this->config->resolveExecOptions($execution);

        $dispatcher = $this->config->getEventDispatcher();
        $processorConfig = $this->config->getProcessorConfig();

        // Dispatch PRE_EXECUTE After resolvedOptions has been set !
        if ($dispatcher->hasListeners(JobEvents::PRE_EXECUTE)) {
            $dispatcher->dispatch(JobEvents::PRE_EXECUTE, new JobEvent($this, $execution));
        }

        if ($processorConfig instanceof ProcessorConfig) {
            $processorConfig
                ->createProcessor($this->config->getMetadataAccessor(), $this->getLogger())
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
            $dispatcher->dispatch(JobEvents::POST_EXECUTE, new JobEvent($this, $execution));
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
     * @param string $name
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
     * @return array
     */
    public function getInitOptions()
    {
        return $this->config->getInitOptions();
    }

    /**
     * @return array
     */
    public function getContextOption()
    {
        return $this->getOption('context', []);
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->config->getOptions();
    }

    /**
     * @param string $name
     * @return array
     */
    public function getOption($name, $default = null)
    {
        return $this->config->getOption($name, $default);
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

    /**
     * @return null|\Psr\Log\LoggerInterface
     */
    public function getLogger()
    {
        return $this->config->getOption('logger');
    }

    public function __toString()
    {
        return $this->getName();
    }

    public function getResolvedInnerType()
    {
        return $this->config->getResolved()->getInnerType();
    }
}
