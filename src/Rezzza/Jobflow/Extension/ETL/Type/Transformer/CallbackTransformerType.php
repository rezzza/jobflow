<?php

namespace Rezzza\Jobflow\Extension\ETL\Type\Transformer;

use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Rezzza\Jobflow\AbstractJobType;

class CallbackTransformerType extends AbstractJobType
{
    public function setExecOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'class' => 'Knp\ETL\Transformer\CallbackTransformer',
            'args' => function(Options $options) {
                return array(
                    'callback' => $options['callback']
                );
            }
        ));

        $resolver->setRequired(array(
            'callback'
        ));

        $resolver->setAllowedTypes(array(
            'callback' => 'callable'
        ));
    }

    public function getName()
    {
        return 'callback_transformer';
    }

    public function getParent()
    {
        return 'transformer';
    }
}