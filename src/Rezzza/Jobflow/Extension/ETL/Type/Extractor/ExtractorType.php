<?php

namespace Rezzza\Jobflow\Extension\ETL\Type\Extractor;

use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Rezzza\Jobflow\Extension\ETL\Type\ETLType;

class ExtractorType extends ETLType
{
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        parent::setDefaultOptions($resolver);

        $resolver->setDefaults(array(
            'offset' => 0,
            'args' => function(Options $options) {
                $io = $options['io'];

                return array(
                    'filename' => $io->stdin->getDsn()
                );
            } 
        ));
    }

    public function getName()
    {
        return 'extractor';
    }

    public function getETLType()
    {
        return self::TYPE_EXTRACTOR;
    }

    public function getProxyClass()
    {
        return 'Rezzza\Jobflow\Extension\ETL\Processor\ExtractorProxy';
    }
}