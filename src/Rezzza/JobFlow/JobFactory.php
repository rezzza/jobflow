<?php

namespace Rezzza\JobFlow;

use Rezzza\JobFlow\Io\IoDescriptor;
use Rezzza\JobFlow\Scheduler\JobFlow;
use Rezzza\JobFlow\Scheduler\TransportInterface;

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
        return $this->createBuilder($type, null, $options)->getJob();
    }

    /**
     * Create a builder
     *
     * @param mixed $type The JobTypeInterface or the alias of the job type registered as a service
     * @param array $options
     *
     * @return JobBuilder
     */
    public function createBuilder($type = 'job', IoDescriptor $io = null, array $options = array())
    {
        $name = $type instanceof JobTypeInterface || $type instanceof ResolvedJob
            ? $type->getName()
            : $type;

        return $this->createNamedBuilder($name, $type, $io, $options);
    }

    /**
     * @param string $name
     * @param mixed $type The JobTypeInterface or the alias of the job type registered as a service
     * @param IoDescriptor $io To connect jobs together
     * @param array $options
     *
     * @return JobBuilder
     */
    public function createNamedBuilder($name, $type = 'job', IoDescriptor $io = null, array $options = array())
    {
        if (is_string($type)) {
            $type = $this->registry->getType($type);
        }

        // We need to avoid this. Io should be injected in the job which required it
        if (null !== $io && !array_key_exists('io', $options)) {
            $options['io'] = $io;
        }

        if ($type instanceof JobTypeInterface) {
            $type = $this->resolveType($type);
        } elseif (!$type instanceof ResolvedJob) {
            throw new \InvalidArgumentException(sprintf('Type "%s" should be a string, JobTypeInterface or ResolvedJob', (is_object($type) ? get_class($type) : $type)));
        }

        return $type->createBuilder($name, $this, $options);
    }

    /**
     * Creates a JobFlow
     *
     * @param string|TransportInterface $transport
     *
     * @return JobFlow
     */
    public function createJobFlow($transport)
    {
        if (is_string($transport)) {
            $transport = $this->registry->getTransport($transport);
        } elseif (!$transport instanceof TransportInterface) {
            throw new \InvalidArgumentException('$transport should a string or a TransportInterface');
        }

        return new JobFlow($transport);
    }

    /**
     * Creates wrapper for combination of JobType and JobConnector
     *
     * @param JobTypeInterface $type
     * @param IoDescriptor $io
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
            $parentType = $this->resolveType($parentType);
        }

        return $this->createResolvedType($type, $parentType);
    }

    public function createResolvedType($type, $parentType)
    {
        return new ResolvedJob($type, $parentType);
    }
}