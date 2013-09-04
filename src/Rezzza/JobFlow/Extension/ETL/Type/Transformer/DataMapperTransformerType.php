<?php

namespace Rezzza\JobFlow\Extension\ETL\Type\Transformer;

use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Rezzza\JobFlow\AbstractJobType;

class DataMapperTransformerType extends AbstractJobType
{
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'class' => 'Knp\ETL\Transformer\DataMap',
            'etl_config' => function(Options $options) {
                $class = $options['class'];

                return array(
                    'transformer' => new $class($options['mapping']),
                );
            }
        ));

        $resolver->setRequired(array(
            'mapping'
        ));
    }

    public function getName()
    {
        return 'datamapper_transformer';
    }

    public function getParent()
    {
        return 'transformer';
    }
}