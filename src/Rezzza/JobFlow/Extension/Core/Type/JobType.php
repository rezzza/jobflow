<?php

namespace Rezzza\JobFlow\Extension\Core\Type;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Rezzza\JobFlow\AbstractJobType;
use Rezzza\JobFlow\JobBuilder;

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
    public function buildJob(JobBuilder $builder, array $options)
    {
        $builder
            ->setIo($options['io'])
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'io' => null,
            'context' => array()
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