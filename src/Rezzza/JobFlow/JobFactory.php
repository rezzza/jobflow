<?php

namespace Rezzza\JobFlow;

use Rezzza\JobFlow\Io\IoDescriptor;
use Rezzza\JobFlow\Io\IoResolver;

/**
 * To create Job or JobBuilder.
 * The started point for the Job component execution.
 * You can called him as a service with `$container->get('job_flow.factory')`
 *
 * @author Timothée Barray <tim@amicalement-web.net>
 */
class JobFactory
{
    /**
     * @var JobRegistry
     */
    protected $registry;

    protected $ioResolver;

    /**
     * @param JobRegistry $registry
     * @param IoResolver $ioResolver
     */
    public function __construct(JobRegistry $registry, IoResolver $ioResolver)
    {
        $this->registry = $registry;
        $this->ioResolver = $ioResolver; 
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

        $io = $this->ioResolver->resolve($io);

        if (null !== $io && !array_key_exists('io', $options)) {
            $options['io'] = $io;
        }

        if ($type instanceof JobTypeInterface) {
            $type = $this->createType($type, $io);
        } elseif (!$type instanceof ResolvedJob) {
            throw new \InvalidArgumentException(sprintf('Type "%s" should be a string, JobTypeInterface or ResolvedJob', $type));
        }

        return $type->createBuilder($name, $this, $options);
    }

    /**
     * Create wrapper for combination of JobType and JobConnector
     *
     * @param JobTypeInterface $type
     * @param IoDescriptor $io
     *
     * @return ResolvedJob
     */
    public function createType(JobTypeInterface $type, IoDescriptor $io)
    {
        $parentType = $type->getParent();

        if ($parentType instanceof JobTypeInterface) {
            $parentType = $this->createType($parentType, $io);
        } elseif (null !== $parentType) {
            $parentType = $this->registry->getType($parentType);
            $parentType = $this->createType($parentType, $io);
        }

        return new ResolvedJob($type, $io, $parentType);
    }
}