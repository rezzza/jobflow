<?php

namespace Rezzza\Jobflow\Extension\ETL\Type\Transformer;

use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Rezzza\Jobflow\Extension\ETL\Type\ETLType;

class TransformerType extends ETLType
{
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        parent::setDefaultOptions($resolver);

        $resolver->setDefaults(array(
            'transform_class' => null,
            'update_method' => null
        ));
    }

    public function getName()
    {
        return 'transformer';
    }

    public function getETLType()
    {
        return self::TYPE_TRANSFORMER;
    }

    public function getProxyClass()
    {
        return 'Rezzza\Jobflow\Extension\ETL\Processor\TransformerProxy';
    }
}