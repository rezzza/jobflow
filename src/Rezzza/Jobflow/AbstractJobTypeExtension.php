<?php

namespace Rezzza\Jobflow;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Rezzza\JobFlow\JobBuilder;
use Rezzza\JobFlow\JobInput;
use Rezzza\JobFlow\JobOutput;
use Rezzza\Jobflow\Scheduler\ExecutionContext;

/**
 * @author Timothée Barray <tim@amicalement-web.net>
 */
abstract class AbstractJobTypeExtension implements JobTypeExtensionInterface
{
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
}