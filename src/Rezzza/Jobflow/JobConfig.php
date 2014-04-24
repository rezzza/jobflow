<?php

namespace Rezzza\Jobflow;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Rezzza\Jobflow\Metadata\MetadataAccessor;
use Rezzza\Jobflow\Processor\ProcessorConfig;
use Rezzza\Jobflow\Scheduler\ExecutionContext;

/**
 * Config the job.
 *
 * @author Timothée Barray <tim@amicalement-web.net>
 */
class JobConfig
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var EventDispatche
     */
    private $dispatcher;

    /**
     * @var ResolvedJob
     */
    private $resolved;

    /**
     * @var processorConfig
     */
    private $processorConfig;

    /**
     * @var MetadataAccessor
     */
    private $metadataAccessor;

    /**
     * @var JobFactory
     */
    private $jobFactory;

    /**
     * @var mixed
     */
    private $requeue;

    /**
     * @var array
     */
    private $attributes;

    /**
     * @var array
     */
    private $initOptions;

    /**
     * @var array
     */
    private $execOptions;

    /**
     * @var array
     */
    private $resolvedExecOptions;

    /**
     * @param string $name
     */
    public function __construct($name, EventDispatcherInterface $dispatcher, array $initOptions = array(), array $execOptions = array())
    {
        $this->name = $name;
        $this->dispatcher = $dispatcher;
        $this->initOptions = $initOptions;
        $this->execOptions = $execOptions;
        $this->resolvedExecOptions = array();
        $this->attributes = array();
    }

    /**
     * {@inheritdoc}
     */
    public function addEventSubscriber(EventSubscriberInterface $subscriber)
    {
        $this->dispatcher->addSubscriber($subscriber);

        return $this;
    }

    /**
     * @return JobConfig
     */
    public function getJobConfig()
    {
        // This method should be idempotent, so clone the builder
        $config = clone $this;

        return $config;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return EventDispatcherInterface
     */
    public function getEventDispatcher()
    {
        return $this->dispatcher;
    }

    /**
     * @return ResolvedJob
     */
    public function getResolved()
    {
        return $this->resolved;
    }

    /**
     * @return ProcessorConfig
     */
    public function getProcessorConfig()
    {
        return $this->processorConfig;
    }

    /**
     * @return MetadataAccessor
     */
    public function getMetadataAccessor()
    {
        return $this->metadataAccessor;
    }

    /**
     * @return JobFactory
     */
    public function getJobFactory()
    {
        return $this->jobFactory;
    }

    public function getRequeue()
    {
        return $this->requeue;
    }

    /**
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * @return boolean
     */
    public function hasAttribute($name)
    {
        return array_key_exists($name, $this->attributes);
    }

    /**
     * @return array
     */
    public function getAttribute($name, $default = null)
    {
        return array_key_exists($name, $this->attributes) ? $this->attributes[$name] : $default;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return array_merge($this->initOptions, $this->resolvedExecOptions);
    }

    /**
     * @return array
     */
    public function getInitOptions()
    {
        return $this->initOptions;
    }

    /**
     * @return array
     */
    public function getExecOptions()
    {
        return $this->execOptions;
    }

    /**
     * @return array
     */
    public function getResolvedExecOptions()
    {
        return $this->resolvedExecOptions;
    }

    /**
     * @return boolean
     */
    public function hasOption($name)
    {
        return array_key_exists($name, $this->getOptions());
    }

    /**
     * @return mixed
     */
    public function getOption($name, $default = null)
    {
        $options = $this->getOptions();

        return array_key_exists($name, $options) ? $options[$name] : $default;
    }

    /**
     * @param ResolvedJob $resolved
     *
     * @return JobConfig
     */
    public function setResolved(ResolvedJob $resolved)
    {
        $this->resolved = $resolved;

        return $this;
    }

    /**
     * @param processorConfig $config
     *
     * @return JobConfig
     */
    public function setProcessorConfig($config)
    {
        $this->processorConfig = $config;

        return $this;
    }

    /**
     * @param MetadataAccessor $accessor
     *
     * @return JobConfig
     */
    public function setMetadataAccessor(MetadataAccessor $accessor)
    {
        $this->metadataAccessor = $accessor;

        return $this;
    }

    /**
     *
     * @return JobConfig
     */
    public function setJobFactory(JobFactory $jobFactory)
    {
        $this->jobFactory = $jobFactory;

        return $this;
    }

    public function setRequeue($requeue)
    {
        $this->requeue = $requeue;

        return $this;
    }

    /**
     * Store objects builded from options
     * @param string $name
     */
    public function setAttribute($name, $value)
    {
        $this->attributes[$name] = $value;

        return $this;
    }

    public function setAttributes(array $attributes)
    {
        $this->attributes = $attributes;

        return $this;
    }

    /**
     * @param array
     */
    public function setInitOptions(array $options)
    {
        $this->initOptions = $options;

        return $this;
    }

    /**
     * @param array
     */
    public function setExecOptions(array $options)
    {
        $this->execOptions = $options;

        return $this;
    }

    /**
     * @param array
     */
    public function setResolvedExecOptions(array $options)
    {
        $this->resolvedExecOptions = $options;

        return $this;
    }

    public function resolveExecOptions(ExecutionContext $execution)
    {
        $options = $this->execOptions;

        // We inject execution here to be able to use it then in JobType options.
        $options['execution'] = $execution;

        $options = $this->resolved->execJob($this, $options);

        $this->setResolvedExecOptions($options);
    }
}
