<?php

namespace Rezzza\JobFlow\Extension\Core\Type\Loader;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Rezzza\JobFlow\AbstractJobType;

class FileLoaderType extends AbstractJobType
{
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'class' => 'Knp\ETL\Loader\FileLoader',
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