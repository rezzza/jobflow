<?php

namespace Rezzza\JobFlow;

use Rezzza\JobFlow\Extension\JobExtensionInterface;
use Rezzza\JobFlow\Io\IoResolver;

class JobFactoryBuilder
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
    private $wrappers = array();

     /**
     * {@inheritdoc}
     */
    public function addExtension(JobExtensionInterface $extension)
    {
        $this->extensions[] = $extension;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addExtensions(array $extensions)
    {
        $this->extensions = array_merge($this->extensions, $extensions);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addType(JobTypeInterface $type)
    {
        $this->types[$type->getName()] = $type;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addTypes(array $types)
    {
        foreach ($types as $type) {
            $this->types[$type->getName()] = $type;
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addWrapper(IoWrapperInterface $wrapper)
    {
        $this->wrappers[$wrapper->getName()] = $wrapper;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addWrappers(array $wrappers)
    {
        foreach ($wrappers as $wrapper) {
            $this->wrappers[$wrapper->getName()] = $wrappers;
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getJobFactory()
    {
        $extensions = $this->extensions;

        if (count($this->types) > 0 || count($this->wrappers) > 0) {
            $base = new BaseExtension();

            foreach ($this->types as $type) {
                $base->addType($type);
            }

            foreach ($this->wrappers as $wrapper) {
                $base->addWrapper($wrapper);
            }

            $extensions[] = $base;
        }

        $registry = new JobRegistry($extensions);
        $resolver = new IoResolver($registry);

        return new JobFactory($registry, $resolver);
    }
}