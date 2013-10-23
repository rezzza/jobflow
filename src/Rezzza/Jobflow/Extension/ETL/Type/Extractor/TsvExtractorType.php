<?php

namespace Rezzza\Jobflow\Extension\ETL\Type\Extractor;

use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Rezzza\Jobflow\AbstractJobType;

class TsvExtractorType extends AbstractJobType
{
    public function setExecOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'args' => function(Options $options) {
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