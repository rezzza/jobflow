<?php

namespace Rezzza\Jobflow;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Rezzza\JobFlow\JobBuilder;

/**
 * @author Timothée Barray <tim@amicalement-web.net>
 */
abstract class AbstractJobTypeExtension implements JobTypeExtensionInterface
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
    public function setInitOptions(OptionsResolverInterface $resolver)
    {
    }

    public function setExecOptions(OptionsResolverInterface $resolver)
    {
    }
}
