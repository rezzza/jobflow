<?php

namespace Rezzza\JobFlow\Extension\ETL\Type;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Rezzza\JobFlow\AbstractJobType;
use Rezzza\JobFlow\JobBuilder;

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
            ->setEtlConfig($options['etl_config'])
            ->setEtlType($options['etl_type'])
        ;
    }

    abstract function getETLType();

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'etl_type' => $this->getETLType()
        ));

        $resolver->setRequired(array(
            'etl_config'
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