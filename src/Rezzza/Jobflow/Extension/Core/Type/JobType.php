<?php

namespace Rezzza\Jobflow\Extension\Core\Type;

use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Rezzza\Jobflow\AbstractJobType;
use Rezzza\Jobflow\Metadata\MessageContainer;
use Rezzza\Jobflow\Metadata\MetadataAccessor;

/**
 * Generic Parent Class for all job type. Generic logic should go here
 *
 * @author Timothée Barray <tim@amicalement-web.net>
 */
class JobType extends AbstractJobType
{
    public function buildConfig($config, $options)
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

    public function setDefaultOptions(OptionsResolverInterface $resolver)
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