<?php

namespace Rezzza\Jobflow\Extension\ETL\Type\Transformer;

use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Rezzza\Jobflow\AbstractJobType;

class DataMapperTransformerType extends AbstractJobType
{
    public function setExecOptions(OptionsResolverInterface $resolver)
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