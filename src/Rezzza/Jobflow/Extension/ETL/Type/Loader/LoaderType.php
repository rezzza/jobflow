<?php

namespace Rezzza\Jobflow\Extension\ETL\Type\Loader;

use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Rezzza\Jobflow\Extension\ETL\Type\ETLType;

class LoaderType extends ETLType
{
    public function setInitOptions(OptionsResolverInterface $resolver)
    {
        parent::setInitOptions($resolver);

        $resolver->setDefaults(array(
            'etl_type' => self::TYPE_LOADER,
            'requeue' => true
        ));
    }
    
    public function setExecOptions(OptionsResolverInterface $resolver)
    {
        parent::setExecOptions($resolver);

        $resolver->setDefaults(array(
            'proxy_class' => 'Rezzza\Jobflow\Extension\ETL\Processor\LoaderProxy',
            'property' => null,
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
}