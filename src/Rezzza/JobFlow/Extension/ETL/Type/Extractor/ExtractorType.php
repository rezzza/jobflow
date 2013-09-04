<?php

namespace Rezzza\JobFlow\Extension\ETL\Type\Extractor;

use Knp\ETL;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Rezzza\JobFlow\Extension\ETL\Type\ETLType;
use Rezzza\JobFlow\JobInput;
use Rezzza\JobFlow\JobOutput;
use Rezzza\JobFlow\Scheduler\ExecutionContext;

class ExtractorType extends ETLType
{
    public function execute(JobInput $input, JobOutput $output, ExecutionContext $execution)
    {
        $input = $input->source;

        if ($this->isLoggable($input) && $execution->getLogger()) {
            $input->setLogger($execution->getLogger());
        }

        if (null === $execution->getOption('total')) {
            $execution->setGlobalOption('total', $input->count());
        }

        $offset = $execution->getOption('offset');
        $limit = $execution->getOption('limit');

        $input->seek($offset);
        $etl = new ETL\Context\Context();

        // Skip Header
        if ($offset === 0) {
            $input->extract($etl);
        }

        for ($i = 0; $i < $limit && $input->valid(); $i++) {
            $output->write($input->current());
            $input->next();
        }

        return $output;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setRequired(array(
            'class'
        ));

        $resolver->setDefaults(array(
            'etl_config' => function(Options $options) {
                $class = $options['class'];
                $io = $options['io'];

                return array(
                    'extractor' => new $class($io->stdin->getDsn())
                );
            } 
        ));
    }

    public function getName()
    {
        return 'extractor';
    }

    public function getETLType()
    {
        return 'extractor';
    }
}