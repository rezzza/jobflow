<?php

namespace Rezzza\JobFlow\Extension\ETL\Type\Extractor;

use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Rezzza\JobFlow\AbstractJobType;

class CsvExtractorType extends AbstractJobType
{
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'class' => 'Knp\ETL\Extractor\CsvExtractor'
        ));
    }

    public function getName()
    {
        return 'csv_extractor';
    }

    public function getParent()
    {
        return 'extractor';
    }
}
