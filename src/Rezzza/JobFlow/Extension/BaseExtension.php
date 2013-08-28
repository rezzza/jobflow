<?php

namespace Rezzza\JobFlow\Extension;

use Rezzza\JobFlow\JobTypeInterface;
use Rezzza\JobFlow\Io\IoWrapperInterface;

abstract class BaseExtension implements JobExtensionInterface
{
    /**
     * @var JobTypeInterface[]
     */
    protected $types;

    /**
     * @var IoWrapperInterface[]
     */
    protected $wrappers;

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

    public function addWrapper(IoWrapperInterface $wrapper)
    {
        $this->wrappers[$wrapper->getName()] = $wrapper;
    }

    /**
     * {@inheritdoc}
     */
    public function getWrapper($name)
    {
        if (null === $this->wrappers) {
            $this->initWrappers();
        }

        if (!isset($this->wrappers[$name])) {
            throw new \InvalidArgumentException(sprintf('The wrapper "%s" can not be loaded by this extension', $name));
        }

        return $this->wrappers[$name];
    }

    /**
     * {@inheritdoc}
     */
    public function hasWrapper($name)
    {
        if (null === $this->wrappers) {
            $this->initWrappers();
        }

        return isset($this->wrappers[$name]);
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
     * @return IoWrapperInterface[]
     */
    protected function loadWrappers()
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
     * Initializes the types.
     */
    private function initWrappers()
    {
        $this->wrappers = array();

        foreach ($this->loadWrappers() as $wrapper) {
            if (!$wrapper instanceof IoWrapperInterface) {
                throw new \InvalidArgumentException(sprintf('Wrapper %s should implements IoWrapperInterface', get_class($wrapper)));
            }

            $this->wrappers[$wrapper->getName()] = $wrapper;
        }
    }
}