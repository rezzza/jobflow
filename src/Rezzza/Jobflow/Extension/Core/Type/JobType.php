<?php

namespace Rezzza\Jobflow\Extension\Core\Type;

use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Rezzza\Jobflow\AbstractJobType;
use Rezzza\Jobflow\Metadata\MessageContainer;
use Rezzza\Jobflow\Metadata\MetadataGenerator;

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
            ->setIo($options['io'])
            ->setMetadataGenerator(new MetadataGenerator($options['metadata']))
            ->setMessageContainer($options['message_container'])
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'io' => null,
            'metadata' => array(),
            'message' => null,
            'message_container' => function (Options $options) {
                return new MessageContainer($options['message']);
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