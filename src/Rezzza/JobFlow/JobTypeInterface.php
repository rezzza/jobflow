<?php

namespace Rezzza\JobFlow;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * @author Timothée Barray <tim@amicalement-web.net>
 */
interface JobTypeInterface
{
    /**
     * Builds the job.
     *
     * This method is called for each type in the hierarchy starting form the
     * top most type.
     *
     * @param JobBuilder $builder The job builder
     * @param array $options The options
     */
    public function buildJob(JobBuilder $builder, array $options);

    /**
     * Sets the default options for this type.
     *
     * @param OptionsResolverInterface $resolver The resolver for the options.
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver);

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName();
}