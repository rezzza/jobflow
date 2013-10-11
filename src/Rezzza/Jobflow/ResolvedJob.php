<?php

namespace Rezzza\Jobflow;

use Symfony\Component\OptionsResolver\OptionsResolver;

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
    private $optionsResolver;

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

    public function configJob($config, $options)
    {
        $options = $this->getOptionsResolver()->resolve($options);

        $this->buildConfig($config, $options);

        return $options;
    }

    /**
     * Init options with innerType requirements
     *
     * @return OptionsResolver
     */
    public function getOptionsResolver()
    {
        if (null === $this->optionsResolver) {
            if (null !== $this->parent) {
                $this->optionsResolver = clone $this->parent->getOptionsResolver();
            } else {
                $this->optionsResolver = new OptionsResolver();
            }

            $this->innerType->setDefaultOptions($this->optionsResolver);
        }

        return $this->optionsResolver;
    }

    /**
     * @param string $name
     * @param JobFactory $factory
     * @param array $options
     *
     * @return JobBuilder
     */
    public function createBuilder($name, JobFactory $factory, array $options = array())
    {
        $builder = $this->newBuilder($name, $factory, $options);

        $builder->setResolved($this);

        $this->buildJob($builder, $options);

        return $builder;
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
    protected function newBuilder($name, JobFactory $factory, array $options)
    {
        return new JobBuilder($name, $factory, $options);
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

    protected function buildConfig($config, $options)
    {
        if (null !== $this->parent) {
            $this->parent->buildConfig($config, $options);
        }

        $this->innerType->buildConfig($config, $options);
    }
}