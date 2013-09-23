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

    /**
     * Execute innerType
     *
     * @param JobContext $context
     *
     * @return boolean
     */
    public function execute($input, $output, $execution)
    {
        $res = $this->innerType->execute($input, $output, $execution);

        // Try to execute parent if no result
        if (null === $res && null !== $this->getParent()) {
            $res = $this->getParent()->execute($input, $output, $execution);
        }

        if (null === $res) {
            throw new \RuntimeException('Job execution should return result');
        }

        return $res;
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

        foreach ($this->typeExtensions as $extension) {
            $extension->buildJob($builder, $options);
        }
    }
}