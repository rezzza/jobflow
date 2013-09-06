<?php

namespace Rezzza\JobFlow\Extension\ETL\Type\Loader;

use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Rezzza\JobFlow\AbstractJobType;

class PipeLoaderType extends AbstractJobType
{
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setRequired(array(
            'mapping'
        ));

        $resolver->setDefaults(array(
            'class' => 'Rezzza\JobFlow\Pipe',
            'etl_config' => function(Options $options) {
                return array(
                    'class' => $options['class'],
                    'args' => array($options['mapping'])
                );
            } 
        ));
    }

    public function getName()
    {
        return 'pipe_loader';
    }

    public function getParent()
    {
        return 'loader';
    }
}