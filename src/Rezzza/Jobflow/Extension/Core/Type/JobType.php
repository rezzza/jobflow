<?php

namespace Rezzza\Jobflow\Extension\Core\Type;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Rezzza\Jobflow\AbstractJobType;
use Rezzza\Jobflow\JobBuilder;
use Rezzza\Jobflow\Metadata\MetadataManager;

/**
 * Doit configurer le Job via son FormConfig setté par le Builder. 
 * On définit des options par défaut ou non et dans le buildJob, 
 * il faut setter au Job ces options
 *
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
            ->setMetadataManager(new MetadataManager($options['metadata_manager']))
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'io' => null,
            'metadata_manager' => array()
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