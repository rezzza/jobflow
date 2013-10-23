<?php

namespace Rezzza\Jobflow\Extension\ETL\Type\Transformer;

use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Rezzza\Jobflow\Extension\ETL\Type\ETLType;

class TransformerType extends ETLType
{
    public function setInitOptions(OptionsResolverInterface $resolver)
    {
        parent::setInitOptions($resolver);

        $resolver->setDefaults(array(
            'etl_type' => self::TYPE_TRANSFORMER
        ));
    }

    public function setExecOptions(OptionsResolverInterface $resolver)
    {
        parent::setExecOptions($resolver);

        $resolver->setDefaults(array(
            'proxy_class' => 'Rezzza\Jobflow\Extension\ETL\Processor\TransformerProxy',
            'transform_class' => null,
            'update_method' => null,
        ));
    }

    public function getName()
    {
        return 'transformer';
    }
}