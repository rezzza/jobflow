<?php

namespace Rezzza\Jobflow\Extension\ETL\Type\Loader;

use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Rezzza\Jobflow\Extension\ETL\Type\ETLType;

class LoaderType extends ETLType
{
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        parent::setDefaultOptions($resolver);

        $resolver->setDefaults(array(
            'args' => function(Options $options) {
                $io = $options['io'];

                return array(
                    'dsn' => $io->stdout->getDsn()
                );
            } 
        ));
    }

    public function getName()
    {
        return 'loader';
    }

    public function getETLType()
    {
        return self::TYPE_LOADER;
    }

    public function getProxyClass()
    {
        return 'Rezzza\Jobflow\Extension\ETL\Processor\LoaderProxy';
    }
}