<?php

namespace Rezzza\Jobflow;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * @author Timothée Barray <tim@amicalement-web.net>
 */
abstract class AbstractJobType implements JobTypeInterface
{
    /**
     * {@inheritdoc}
     */
    public function buildJob(JobBuilder $builder, array $options)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function buildConfig(JobConfig $config, $options)
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