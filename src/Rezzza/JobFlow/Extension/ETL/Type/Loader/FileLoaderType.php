<?php

namespace Rezzza\JobFlow\Extension\ETL\Type\Loader;

use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Rezzza\JobFlow\AbstractJobType;

class FileLoaderType extends AbstractJobType
{
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'class' => 'Knp\ETL\Loader\FileLoader',
            'etl_config' => function(Options $options) {
                $class = $options['class'];
                $io = $options['io'];
                $file = new \SplFileObject($io->stdout->getDsn(), 'a+');

                return array(
                    'class' => $class,
                    'args' => array($file)
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