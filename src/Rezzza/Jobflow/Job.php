<?php

namespace Rezzza\Jobflow;

use Rezzza\Jobflow\Event\JobEvent;
use Rezzza\Jobflow\Event\JobEvents;
use Rezzza\Jobflow\Extension\ETL\Type\ETLType;
use Rezzza\Jobflow\Scheduler\ExecutionContext;
use Rezzza\Jobflow\Processor\ConfigProcessor;

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
     * @var ExecutionContext $context
     */
    public function execute(ExecutionContext $context)
    {
        // We inject msg as it could be used during job runtime configuration
        $options = $this->getExecOptions();
        $options['message'] = $context->getInput()->getMessage();

        // Runtime configuration (!= buildJob which is executed when we build job)
        $options = $this->getResolved()->execJob($this->getConfig(), $options);
        // Should avoid this kind of operations. ConfigJob Runtime need to be improve.
        $this->getConfig()->setResolvedExecOptions($options);

        $dispatcher = $this->config->getEventDispatcher();

        if ($dispatcher->hasListeners(JobEvents::PRE_EXECUTE)) {
            $event = new JobEvent($this);
            $dispatcher->dispatch(JobEvents::PRE_EXECUTE, $event);
        }

        $input = $context->getInput();
        $output = $context->getOutput();
        $config = $this->getConfig()->getConfigProcessor();

        if ($config instanceof ConfigProcessor) {
            $factory = new \Rezzza\Jobflow\Processor\ProcessorFactory;
            $factory
                ->create($input->getMessage()->pipe, $config, $this->getConfig()->getMetadataAccessor())
                ->execute($input, $output, $context)
            ;
        } elseif (is_callable($config)) {
            call_user_func_array(
                $config,
                array(
                    $input,
                    $output,
                    $context
                )
            );
        } else {
            throw new \InvalidArgumentException('processor should be a ConfigProcessor or a callable');
        }

        // Update context
        $output->setContextFromInput($input);

        if ($dispatcher->hasListeners(JobEvents::POST_EXECUTE)) {
            $event = new JobEvent($this);
            $dispatcher->dispatch(JobEvents::POST_EXECUTE, $event);
        }

        return $output;
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
