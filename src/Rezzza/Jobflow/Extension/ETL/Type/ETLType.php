<?php

namespace Rezzza\Jobflow\Extension\ETL\Type;

use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Rezzza\Jobflow\AbstractJobType;
use Rezzza\Jobflow\JobBuilder;

/**
 * For all type based on ETL pattern we need to specify the step of the process
 *
 * @author Timothée Barray <tim@amicalement-web.net>
 */
abstract class ETLType extends AbstractJobType
{
    const TYPE_EXTRACTOR = 'extractor';
    const TYPE_TRANSFORMER = 'transformer';
    const TYPE_LOADER = 'loader';

    public function buildJob(JobBuilder $builder, array $options)
    {
        $builder
            ->setEtlType($this->getETLType())
        ;
    }

    public function buildConfig($config, $options)
    {
        $config
            ->setEtlConfig($options['etl_config'])
        ;
    }

    abstract function getETLType();

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setRequired(array(
            'class'
        ));

        $resolver->setDefaults(array(
            'args' => array(),
            'etl_config' => function(Options $options) {
                return array(
                    'class' => $options['class'],
                    'args' => $options['args']
                );
            } 
        ));
    }

    protected function isLoggable($object)
    {
        if (!is_object($object)) {
            return false;
        }

        return in_array('Psr\Log\LoggerAwareTrait', class_uses(get_class($object)));
    }
}