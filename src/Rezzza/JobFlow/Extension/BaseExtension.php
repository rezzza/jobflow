<?php

namespace Rezzza\JobFlow\Extension;

use Rezzza\JobFlow\JobTypeInterface;
use Rezzza\JobFlow\Scheduler\TransportInterface;

class BaseExtension implements JobExtensionInterface
{
    /**
     * @var JobTypeInterface[]
     */
    protected $types;

    /**
     * @var TransportInterface[]
     */
    protected $transports;

    public function addType(JobTypeInterface $type)
    {
        $this->types[$type->getName()] = $type;
    }

    /**
     * {@inheritdoc}
     */
    public function getType($name)
    {
        if (null === $this->types) {
            $this->initTypes();
        }

        if (!isset($this->types[$name])) {
            throw new \InvalidArgumentException(sprintf('The type "%s" can not be loaded by this extension', $name));
        }

        return $this->types[$name];
    }

    /**
     * {@inheritdoc}
     */
    public function hasType($name)
    {
        if (null === $this->types) {
            $this->initTypes();
        }

        return isset($this->types[$name]);
    }

    public function addTransport(TransportInterface $transport)
    {
        $this->transports[$transport->getName()] = $transport;
    }

    /**
     * {@inheritdoc}
     */
    public function getTransport($name)
    {
        if (null === $this->transports) {
            $this->initTransports();
        }

        if (!isset($this->transports[$name])) {
            throw new \InvalidArgumentException(sprintf('The transport "%s" can not be loaded by this extension', $name));
        }

        return $this->transports[$name];
    }

    /**
     * {@inheritdoc}
     */
    public function hasTransport($name)
    {
        if (null === $this->transports) {
            $this->initTransports();
        }

        return isset($this->transports[$name]);
    }

    /**
     * Registers the types.
     *
     * @return JobTypeInterface[]
     */
    protected function loadTypes()
    {
        return array();
    }

    /**
     * Registers the wrappers.
     *
     * @return TransportInterface[]
     */
    protected function loadTransports()
    {
        return array();
    }
    
    /**
     * Initializes the types.
     */
    private function initTypes()
    {
        $this->types = array();

        foreach ($this->loadTypes() as $type) {
            if (!$type instanceof JobTypeInterface) {
                throw new \InvalidArgumentException(sprintf('Type %s should implements JobTypeInterface', get_class($type)));
            }

            $this->types[$type->getName()] = $type;
        }
    }
    
    /**
     * Initializes the transports.
     */
    private function initTransports()
    {
        $this->transports = array();

        foreach ($this->loadTransports() as $transport) {
            if (!$transport instanceof TransportInterface) {
                throw new \InvalidArgumentException(sprintf('Transport %s should implements TransportInterface', get_class($transport)));
            }

            $this->transports[$transport->getName()] = $transport;
        }
    }
}