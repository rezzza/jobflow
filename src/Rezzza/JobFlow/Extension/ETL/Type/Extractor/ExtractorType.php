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

        $execution->setGlobalOption('total', $input->count());

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
        $type = $this;

        $resolver->setRequired(array(
            'class'
        ));

        // On fait passser la class au Job !
        // C'est lui qui fera l'instanciation et fera passer le loader via l'output
        $resolver->setDefaults(array(
            'etl_config' => function(Options $options) use ($type) { 
                return array(
                    'extractor' => $options['class']
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