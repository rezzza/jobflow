<?php

namespace Rezzza\JobFlow;

use Symfony\Component\OptionsResolver\OptionsResolver;

use Rezzza\JobFlow\Io\IoDescriptor;

/**
 * Wrap JobType and IoDescriptor
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
     * @var IoDescriptor
     */
    private $io;

    /**
     * @var ResolvedJob
     */
    private $parent;

    /**
     * @var OptionsResolverInterface
     */
    private $optionsResolver;

    public function __construct(JobTypeInterface $innerType, IoDescriptor $io, ResolvedJob $parent = null)
    {
        $this->innerType = $innerType;
        $this->io = $io;
        $this->parent = $parent;
    }

    /**
     * Execute innerType
     *
     * @param JobContext $context
     *
     * @return boolean
     */
    public function execute($input, $execution)
    {
        return $this->innerType->execute($input, $execution);
    }

    /**
     * @return [ResolvedJob|null]
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @return IoDescriptor
     */
    public function getIo()
    {
        return $this->io;
    }

    /**
     * @return JobTypeInterface
     */
    public function getInnerType()
    {
        return $this->innerType;
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
        $options = $this->getOptionsResolver()->resolve($options);

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
    }
}