<?php

namespace Rezzza\Jobflow\Scheduler;

use Psr\Log\LoggerInterface;

use Rezzza\Jobflow\JobFactory;
use Rezzza\Jobflow\JobRegistry;

class JobflowFactory
{
    /**
     * @var JobRegistry
     */
    protected $registry;

    /**
     * @var JobFactory
     */
    protected $jobFactory;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param JobRegistry $registry
     */
    public function __construct(JobRegistry $registry, JobFactory $jobFactory, LoggerInterface $logger = null)
    {
        $this->registry = $registry;
        $this->jobFactory = $jobFactory;
        $this->logger = $logger;
    }

    /**
     * Creates a Jobflow
     *
     * @param string|TransportInterface $transport
     *
     * @return Jobflow
     */
    public function create($transport)
    {
        if (is_string($transport)) {
            $transport = $this->registry->getTransport($transport);
        } elseif (!$transport instanceof TransportInterface) {
            throw new \InvalidArgumentException('transport should a string or a TransportInterface');
        }

        // If MonologExtension loaded, we inject its logger in Jobflow
        if (null !== ($extension = $this->registry->getExtension('Rezzza\Jobflow\Extension\Monolog\MonologExtension'))) {
            $this->logger = $extension->getLogger();
        }

        return new Jobflow($transport, $this->jobFactory, $this->logger);
    }
}