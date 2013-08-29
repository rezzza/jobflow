<?php

namespace Rezzza\JobFlow;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Rezzza\JobFlow\Scheduler\ExecutionContext;

/**
 * @author Timothée Barray <tim@amicalement-web.net>
 */
abstract class AbstractJobType implements JobTypeInterface
{
    /**
     * {@inheritdoc}
     */
    public function execute($input, ExecutionContext $execution)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function buildJob(JobBuilder $builder, array $options)
    {
    }
    
    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'job';
    }
}