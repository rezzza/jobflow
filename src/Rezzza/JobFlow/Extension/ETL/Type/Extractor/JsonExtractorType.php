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
            'start_node' => null,
            'adapter' => null,
            'etl_config' => function(Options $options) {
                $class = $options['class'];
                $io = $options['io'];

                return array(
                    'extractor' => new $class($io->stdin->getDsn(), $options['start_node'], $options['adapter'])
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
