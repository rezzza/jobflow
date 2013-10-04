<?php

namespace Rezzza\Jobflow;

use Rezzza\Jobflow\Io\IoDescriptor;

/**
 * Wraps all properties we want to pass from builder to job.
 * Mainly use by buildJob method in JobType
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
     * @var ResolvedJob
     */
    private $resolved;

    /**
     * @var array
     */
    private $etlConfig;

    /**
     * @var string
     */
    private $etlType;

    /**
     * @var IoDescriptor
     */
    private $io;

    /**
     * @var array
     */
    private $options;

    private $logger;

    private $metadataManager;

    /**
     * @param string $name
     * @param array $options
     */
    public function __construct($name, array $options = array())
    {
        $this->name = $name;
        $this->options = $options;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return ResolvedJob
     */
    public function getResolved()
    {
        return $this->resolved;
    }

    /**
     * @return array
     */
    public function getEtlConfig()
    {
        return $this->etlConfig;
    }

    /**
     * @return string
     */
    public function getEtlType()
    {
        return $this->etlType;
    }

    /**
     * @return IoDescriptor
     */
    public function getIo()
    {
        return $this->io;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @return boolean
     */
    public function hasOption($name)
    {
        return array_key_exists($name, $this->options);
    }

    /**
     * @return mixed
     */
    public function getOption($name, $default = null)
    {
        return array_key_exists($name, $this->options) ? $this->options[$name] : $default;
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
     * @param array $etlConfig
     *
     * @return JobConfig
     */
    public function setEtlConfig(array $etlConfig)
    {
        $this->etlConfig = $etlConfig;

        return $this;
    }

    /**
     * @param string $etlType
     *
     * @return JobConfig
     */
    public function setEtlType($etlType)
    {
        $this->etlType = $etlType;

        return $this;
    }

    /**
     * @param IoDescriptor $io
     *
     * @return JobConfig
     */
    public function setIo(IoDescriptor $io = null)
    {
        $this->io = $io;

        return $this;
    }

    /**
     * @param JobFactory $etlConfig
     *
     * @return JobConfig
     */
    public function setJobFactory(JobFactory $jobFactory)
    {
        $this->jobFactory = $jobFactory;

        return $this;
    }

    public function setLogger($logger)
    {
        $this->logger = $logger;

        return $this;
    }

    public function getLogger()
    {
        return $this->logger;
    }

    public function setMetadataManager($manager)
    {
        $this->metadataManager = $manager;
    }

    public function getMetadataManager()
    {
        return $this->metadataManager;
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
}