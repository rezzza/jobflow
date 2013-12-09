<?php

namespace Rezzza\Jobflow\Extension\Core\Type;

use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Rezzza\Jobflow\AbstractJobType;
use Rezzza\Jobflow\JobBuilder;
use Rezzza\Jobflow\JobConfig;
use Rezzza\Jobflow\Metadata\MetadataAccessor;

/**
 * Generic Parent Class for all job type. Generic logic should go here
 *
 * @author Timothée Barray <tim@amicalement-web.net>
 */
class JobType extends AbstractJobType
{
    /**
     * {@inheritdoc}
     */
    public function buildJob(JobBuilder $builder, array $options)
    {
        $builder
            ->setRequeue($options['requeue'])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function buildExec(JobConfig $config, array $options)
    {
        $config
            ->setMetadataAccessor(
                new MetadataAccessor(
                    $options['metadata_read'],
                    $options['metadata_write']
                )
            )
            ->setConfigProcessor($options['processor'])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function setExecOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'class' => null,
            'args' => array(),
            'calls' => array(),
            'metadata_read' => array(),
            'metadata_write' => array(),
            'message' => null,
            'processor' => function(Options $options) {
                return new ConfigProcessor(
                    $options['class'],
                    $options['args'],
                    $options['calls']
                );
            }
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function setInitOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'context' => array(),
            'requeue' => null
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'job';
    }
}
