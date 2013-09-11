<?php

namespace Rezzza\JobFlow\Extension\ETL\Type\Extractor;

use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Rezzza\JobFlow\AbstractJobType;

class TsvExtractorType extends AbstractJobType
{
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'args' => function(Options $options) use ($type) {
                $io = $options['io'];

                return array(
                    'dsn' => $io->stdin->getDsn(), 
                    'delimiter' => "\t"
                );
            } 
        ));
    }

    public function getName()
    {
        return 'tsv_extractor';
    }

    public function getParent()
    {
        return 'csv_extractor';
    }
}