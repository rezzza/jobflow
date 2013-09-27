<?php

namespace Rezzza\Jobflow;

use Rezzza\Jobflow\Extension\BaseExtension;
use Rezzza\Jobflow\Extension\JobExtensionInterface;
use Rezzza\Jobflow\Scheduler\JobflowFactory;
use Rezzza\Jobflow\Scheduler\TransportInterface;

class JobsBuilder
{
    /**
     * @var array
     */
    private $extensions = array();

    /**
     * @var array
     */
    private $types = array();

    /**
     * @var array
     */
    private $transports = array();

    public function addExtension(JobExtensionInterface $extension)
    {
        $this->extensions[] = $extension;

        return $this;
    }

    public function addExtensions(array $extensions)
    {
        $this->extensions = array_merge($this->extensions, $extensions);

        return $this;
    }

    public function addType(JobTypeInterface $type)
    {
        $this->types[$type->getName()] = $type;

        return $this;
    }

    public function addTypes(array $types)
    {
        foreach ($types as $type) {
            $this->addType($type);
        }

        return $this;
    }

    public function addTransport(TransportInterface $transport)
    {
        $this->transport[$transport->getName()] = $transport;

        return $this;
    }

    public function addTransports(array $transports)
    {
        foreach ($transports as $transport) {
            $this->addTransport($transport);
        }

        return $this;
    }

    public function getJobRegistry()
    {
        $extensions = $this->extensions;

        if (count($this->types) > 0 || count($this->transports) > 0) {
            $base = new BaseExtension();

            foreach ($this->types as $type) {
                $base->addType($type);
            }

            foreach ($this->transports as $transport) {
                $base->addTransport($transport);
            }

            $extensions[] = $base;
        }

        return new JobRegistry($extensions);
    }

    public function getJobFactory()
    {
        return new JobFactory($this->getJobRegistry());
    }

    public function getJobflowFactory()
    {
        return new JobflowFactory($this->getJobRegistry(), $this->getJobFactory());
    }
}