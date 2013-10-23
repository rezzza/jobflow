<?php

namespace Rezzza\Jobflow;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * a JobType instance helps to create and configure a Job in two stage :
 * - At initialization time : When we build the entire job
 * - At runtime : When we execute a specified job
 *
 * @author Timothée Barray <tim@amicalement-web.net>
 */
interface JobTypeInterface
{
    /**
     * Builds the job
     *
     * @param JobBuilder $builder The job builder
     * @param array $options The options
     */
    public function buildJob(JobBuilder $builder, array $options);

    /**
     * Configs the job for runtime execution
     *
     * @param JobConfig $config The job config
     * @param array $options The options
     */
    public function buildExec(JobConfig $config, array $options);

    /**
     * Sets the default init options for this type.
     *
     * @param OptionsResolverInterface $resolver The resolver for the options.
     */
    public function setInitOptions(OptionsResolverInterface $resolver);

    /**
     * Sets the default exec options for this type.
     *
     * @param OptionsResolverInterface $resolver The resolver for the options.
     */
    public function setExecOptions(OptionsResolverInterface $resolver);

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName();
}