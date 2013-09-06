<?php

namespace Rezzza\JobFlow\Extension\ETL\Type\Extractor;

use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Rezzza\JobFlow\AbstractJobType;

class JsonExtractorType extends AbstractJobType
{
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'class' => 'Knp\ETL\Extractor\JsonExtractor',
            'path' => null,
            'adapter' => null,
            'etl_config' => function(Options $options) {
                $io = $options['io'];

                return array(
                    'class' => $options['class'],
                    'args' => array(
                        'dsn' => $io ? $io->stdin->getDsn() : null, 
                        'path' => $options['path'],
                        'adapter' => $options['adapter']
                    )
                );
            } 
        ));
    }

    public function getName()
    {
        return 'json_extractor';
    }

    public function getParent()
    {
        return 'extractor';
    }
}
