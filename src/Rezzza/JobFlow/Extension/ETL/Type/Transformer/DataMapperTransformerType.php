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
            'args' => function(Options $options) {
                return array(
                    'mapping' => $options['mapping']
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