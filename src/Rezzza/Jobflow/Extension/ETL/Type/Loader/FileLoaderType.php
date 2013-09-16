<?php

namespace Rezzza\Jobflow\Extension\ETL\Type\Loader;

use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Rezzza\Jobflow\AbstractJobType;
use Rezzza\Jobflow\DelayedArg;

class FileLoaderType extends AbstractJobType
{
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'class' => 'Knp\ETL\Loader\FileLoader',
            'args' => function(Options $options) {
                $io = $options['io'];

                $file = function() use ($io) {
                    return new \SplFileObject($io->stdout->getDsn(), 'a+');
                };

                return array(
                    'file' => new DelayedArg($file)
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