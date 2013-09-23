<?php

namespace Rezzza\Jobflow\Scheduler;

use Rezzza\Jobflow\JobFactory;
use Rezzza\Jobflow\JobRegistry;

class JobflowFactory
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

        $logger = null;

        // If MonologExtension loaded, we inject its logger in Jobflow
        if (null !== ($extension = $this->registry->getExtension('Rezzza\Jobflow\Extension\Monolog\MonologExtension'))) {
            $logger = $extension->getLogger();
        }

        return new Jobflow($transport, new JobFactory($this->registry), $logger);
    }
}