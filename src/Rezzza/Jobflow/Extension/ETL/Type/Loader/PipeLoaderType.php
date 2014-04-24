<?php

namespace Rezzza\Jobflow\Extension\ETL\Type\Loader;

use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Rezzza\Jobflow\AbstractJobType;

class PipeLoaderType extends AbstractJobType
{
    public function setExecOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setRequired([
            'forward'
        ]);

        $resolver->setDefaults([
            'class' => 'Rezzza\Jobflow\Extension\Pipe\PipeLoader',
            'args' => function(Options $options) {
                return [
                    'forward' => $options['forward'],
                    'execution' => $options['execution']
                ];
            }
        ]);
    }

    public function getName()
    {
        return 'pipe_loader';
    }

    public function getParent()
    {
        return 'loader';
    }
}