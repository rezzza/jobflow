<?php

namespace Rezzza\Jobflow\Extension\ETL\Type\Extractor;

use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Rezzza\Jobflow\AbstractJobType;

class CsvExtractorType extends AbstractJobType
{
    public function setExecOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'class' => 'Knp\ETL\Extractor\CsvExtractor'
        ]);
    }

    public function getName()
    {
        return 'csv_extractor';
    }

    public function getParent()
    {
        return 'file_extractor';
    }
}
