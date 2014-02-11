<?php

namespace Rezzza\Jobflow\Extension\ETL\Type\Extractor;

use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Rezzza\Jobflow\AbstractJobType;

class TsvExtractorType extends AbstractJobType
{
    public function setExecOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'args' => function(Options $options) {
                $io = $options['io'];

                return [
                    'dsn' => $io->getStdin()->getDriver()->getDsn(),
                    'delimiter' => "\t"
                ];
            }
        ]);
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
