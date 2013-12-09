<?php

namespace Rezzza\Jobflow;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Rezzza\Jobflow\JobConfig;

/**
 * Wraps JobType and builder
 *
 * @author Timothée Barray <tim@amicalement-web.net>
 */
class ResolvedJob
{
    /**
     * @var JobTypeInterface
     */
    private $innerType;

    /**
     * @var ResolvedJob
     */
    private $parent;

    /**
     * @var OptionsResolverInterface
     */
    private $initOptionsResolver;

    /**
     * @var OptionsResolverInterface
     */
    private $execOptionsResolver;

    /**
     * @var JobTypeExtensionInterface[]
     */
    private $typeExtensions;

    public function __construct(JobTypeInterface $innerType, array $typeExtensions = array(), ResolvedJob $parent = null)
    {
        $this->innerType = $innerType;
        $this->typeExtensions = $typeExtensions;
        $this->parent = $parent;
    }

    /**
     * @return [ResolvedJob|null]
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @return JobTypeInterface
     */
    public function getInnerType()
    {
        return $this->innerType;
    }

    public function execJob(JobConfig $config, $options)
    {
        $options = $this->getExecOptionsResolver()->resolve($options);

        $this->buildExec($config, $options);

        return $options;
    }

    /**
     * @param string $name
     * @param JobFactory $factory
     * @param array $options
     *
     * @return JobBuilder
     */
    public function createBuilder($name, JobFactory $factory, array $initOptions = array(), array $execOptions = array())
    {
        $initOptions = $this->getInitOptionsResolver()->resolve($initOptions);

        $builder = $this->newBuilder($name, $factory, $initOptions, $execOptions);

        $builder->setResolved($this);

        $this->buildJob($builder, $initOptions);

        return $builder;
    }

    /**
     * Init options with innerType requirements
     *
     * @return OptionsResolver
     */
    public function getInitOptionsResolver()
    {
        if (null === $this->initOptionsResolver) {
            if (null !== $this->parent) {
                $this->initOptionsResolver = clone $this->parent->getInitOptionsResolver();
            } else {
                $this->initOptionsResolver = new OptionsResolver();
            }

            $this->innerType->setInitOptions($this->initOptionsResolver);
        }

        return $this->initOptionsResolver;
    }

    /**
     * Exec options with innerType requirements
     *
     * @return OptionsResolver
     */
    public function getExecOptionsResolver()
    {
        if (null === $this->execOptionsResolver) {
            if (null !== $this->parent) {
                $this->execOptionsResolver = clone $this->parent->getExecOptionsResolver();
            } else {
                $this->execOptionsResolver = new OptionsResolver();
            }

            $this->innerType->setExecOptions($this->execOptionsResolver);
        }

        return $this->execOptionsResolver;
    }

    /**
     * Create new JobBuilder for the innerType
     *
     * @param string $name
     * @param JobFactory $factory
     * @param array $options
     *
     * @return JobBuilder
     */
    protected function newBuilder($name, JobFactory $factory, array $initOptions, array $execOptions)
    {
        return new JobBuilder($name, $factory, new EventDispatcher(), $initOptions, $execOptions);
    }

    /**
     * @param JobBuilder $builder
     * @param array $options
     */
    protected function buildJob(JobBuilder $builder, array $options)
    {
        if (null !== $this->parent) {
            $this->parent->buildJob($builder, $options);
        }

        $this->innerType->buildJob($builder, $options);

        foreach ($this->typeExtensions as $extension) {
            $extension->buildJob($builder, $options);
        }
    }

    protected function buildExec(JobConfig $config, $options)
    {
        if (null !== $this->parent) {
            $this->parent->buildExec($config, $options);
        }

        $this->innerType->buildExec($config, $options);
    }
}
