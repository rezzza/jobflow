<?php

namespace Rezzza\JobFlow\Extension\ETL\Type\Loader;

use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Rezzza\JobFlow\Extension\ETL\Type\ETLType;
use Rezzza\JobFlow\JobInput;
use Rezzza\JobFlow\JobOutput;
use Rezzza\JobFlow\Scheduler\ExecutionContext;

class LoaderType extends ETLType
{
    public function execute(JobInput $input, JobOutput $output, ExecutionContext $execution)
    {
        if ($this->isLoggable($output->getDestination()) && $execution->getLogger()) {
            $output->getDestination()->setLogger($execution->getLogger());
        }

        foreach ($input->getData() as $d) {
            $output->write($d);
        }

        return $output; // End chain should return empty array
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        parent::setDefaultOptions($resolver);
        
        $resolver->setRequired(array(
            'class'
        ));

        // On fait passser la class au Job !
        // C'est lui qui fera l'instanciation et fera passer le loader via l'output
        $resolver->setDefaults(array(
            'etl_config' => function(Options $options) {
                $io = $options['io'];

                return array(
                    'class' => $options['class'],
                    'args' => array($io->stdout->getDsn())
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