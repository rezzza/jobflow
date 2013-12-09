<?php

namespace Rezzza\Jobflow\Extension\ETL\Type;

use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Rezzza\Jobflow\AbstractJobType;
use Rezzza\Jobflow\Extension\ETL\Processor\ETLConfigProcessor;
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
            ->setAttribute('etl_type', $options['etl_type'])
        ;
    }

    public function setInitOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setRequired(array(
            'etl_type'
        ));
    }

    public function setExecOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setRequired(array(
            'proxy_class'
        ));

        $resolver->setDefaults(array(
            'io' => null,
            'processor' => function(Options $options) {
                return new ETLConfigProcessor(
                    $options['class'],
                    $options['args'],
                    $options['calls'],
                    $options['proxy_class']
                );
            }
        ));
    }

    protected function isLoggable($object)
    {
        if (!is_object($object)) {
            return false;
        }

        return in_array('setLogger', get_class_methods(get_class($object)));
    }
}
