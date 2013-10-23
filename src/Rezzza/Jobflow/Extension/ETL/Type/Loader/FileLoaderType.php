<?php

namespace Rezzza\Jobflow\Extension\ETL\Type\Loader;

use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Rezzza\Jobflow\AbstractJobType;

class FileLoaderType extends AbstractJobType
{
    public function setExecOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'class' => 'Knp\ETL\Loader\FileLoader',
            'args' => function(Options $options) {
                $io = $options['io'];

                return array(
                    'file' =>  new \SplFileObject($io->stdout->getDsn(), 'a+')
                );
            }
        ));
    }

    public function getName()
    {
        return 'file_loader';
    }

    public function getParent()
    {
        return 'loader';
    }
}