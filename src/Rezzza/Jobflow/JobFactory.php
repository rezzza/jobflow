<?php

namespace Rezzza\Jobflow;

/**
 * To create Job or JobBuilder.
 * The started point for the Job component execution.
 *
 * @author Timothée Barray <tim@amicalement-web.net>
 */
class JobFactory
{
    /**
     * @var JobRegistry
     */
    protected $registry;

    /**
     * @param JobRegistry $registry
     */
    public function __construct(JobRegistry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * Create a job
     *
     * @param mixed $type The JobTypeInterface or the alias of the job type registered as a service
     * @param array $options
     *
     * @return Job
     */
    public function create($type, array $options = array())
    {
        return $this->createBuilder($type, $options)->getJob();
    }

    /**
     * Create a builder
     *
     * @param mixed $type The JobTypeInterface or the alias of the job type registered as a service
     * @param array $options
     *
     * @return JobBuilder
     */
    public function createBuilder($type = 'job', array $options = array())
    {
        $name = $type instanceof JobTypeInterface || $type instanceof ResolvedJob
            ? $type->getName()
            : $type;

        return $this->createNamedBuilder($name, $type, $options);
    }

    /**
     * @param string $name
     * @param mixed $type The JobTypeInterface or the alias of the job type registered as a service
     * @param array $options
     *
     * @return JobBuilder
     */
    public function createNamedBuilder($name, $type = 'job', array $options = array())
    {
        if (is_string($type)) {
            $type = $this->registry->getType($type);
        }

        if ($type instanceof JobTypeInterface) {
            $type = $this->resolveType($type);
        } elseif (!$type instanceof ResolvedJob) {
            throw new \InvalidArgumentException(sprintf('Type "%s" should be a string, JobTypeInterface or ResolvedJob', (is_object($type) ? get_class($type) : $type)));
        }

        return $type->createBuilder($name, $this, $options);
    }

    /**
     * Creates wrapper for combination of JobType and JobConnector
     *
     * @param JobTypeInterface $type
     *
     * @return ResolvedJob
     */
    public function resolveType(JobTypeInterface $type)
    {
        $parentType = $type->getParent();

        if ($parentType instanceof JobTypeInterface) {
            $parentType = $this->resolveType($parentType);
        } elseif (null !== $parentType) {
            $parentType = $this->registry->getType($parentType);
        }

        return $this->createResolvedType($type, $parentType);
    }

    public function createResolvedType($type, $parentType)
    {
        return new ResolvedJob($type, array(), $parentType);
    }
}