<?php

namespace Rezzza\Jobflow\Extension\ETL\Type\Loader;

use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Rezzza\Jobflow\Extension\ETL\Type\ETLType;
use Rezzza\Jobflow\JobInput;
use Rezzza\Jobflow\JobOutput;
use Rezzza\Jobflow\Scheduler\ExecutionContext;

class LoaderType extends ETLType
{
    public function execute(JobInput $input, JobOutput $output, ExecutionContext $execution)
    {
        if ($this->isLoggable($output->getDestination()) && $execution->getLogger()) {
            $output->getDestination()->setLogger($execution->getLogger());
        }

        foreach ($input->getData() as $k => $d) {
            $output->write($d, $k);
        }

        // Should not use Events ? Will be more flexible
        $output->finish();

        return $output; // End chain should return empty array
    }

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
}