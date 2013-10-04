<?php

namespace Rezzza\Jobflow;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Rezzza\Jobflow\Scheduler\ExecutionContext;

/**
 * @author Timothée Barray <tim@amicalement-web.net>
 */
abstract class AbstractJobType implements JobTypeInterface
{
    /**
     * {@inheritdoc}
     */
    public function buildConfig($config, $options)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function execute(JobInput $input, JobOutput $output, ExecutionContext $execution)
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

    public function getContextOptions()
    {
        return array();
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'job';
    }
}