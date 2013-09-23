<?php

namespace Rezzza\Jobflow\Extension;

use Rezzza\Jobflow\JobTypeInterface;
use Rezzza\Jobflow\JobTypeExtensionInterface;
use Rezzza\Jobflow\Scheduler\TransportInterface;

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

    /**
     * @var JobTypeExtensionInterface[] 
     */
    protected $typeExtensions;

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
     * {@inheritdoc}
     */
    public function getTypeExtensions($name)
    {
        if (null === $this->typeExtensions) {
            $this->initTypeExtensions();
        }

        return isset($this->typeExtensions[$name])
            ? $this->typeExtensions[$name]
            : array();
    }

    /**
     * {@inheritdoc}
     */
    public function hasTypeExtensions($name)
    {
        if (null === $this->typeExtensions) {
            $this->initTypeExtensions();
        }

        return isset($this->typeExtensions[$name]) && count($this->typeExtensions[$name]) > 0;
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
     * Registers the transports.
     *
     * @return TransportInterface[]
     */
    protected function loadTransports()
    {
        return array();
    }

    protected function loadTypeExtensions()
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

    /**
     * Initializes the type extensions.
     */
    private function initTypeExtensions()
    {
        $this->typeExtensions = array();

        foreach ($this->loadTypeExtensions() as $extension) {
            if (!$extension instanceof JobTypeExtensionInterface) {
                throw new \InvalidArgumentException(sprintf('Extension %s should implements JobTypeExtensionInterface', get_class($extension)));
            }

            $type = $extension->getExtendedType();

            $this->typeExtensions[$type][] = $extension;
        }
    }
}