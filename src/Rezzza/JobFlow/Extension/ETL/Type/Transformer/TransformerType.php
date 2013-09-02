<?php

namespace Rezzza\JobFlow\Extension\ETL\Type\Transformer;

use Knp\ETL;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Rezzza\JobFlow\Extension\ETL\Type\ETLType;
use Rezzza\JobFlow\JobBuilder;
use Rezzza\JobFlow\JobInput;
use Rezzza\JobFlow\JobOutput;
use Rezzza\JobFlow\Scheduler\ExecutionContext;

class TransformerType extends ETLType
{
    protected $transformer;
    
    public function buildJob(JobBuilder $builder, array $options)
    {
        $args = $options['args'];
        $class = $options['class'];
        $this->transformer = new $class($args);
    }

    public function execute(JobInput $input, JobOutput $output, ExecutionContext $execution)
    {
        foreach ($input->source as $k => $result) {
            if ($execution->getLogger()) {
                $execution->getLogger()->debug('transformation '.$k);
            }
            
            $etlContext = new ETL\Context\Context();

            $output->write($this->transformer->transform($result, $etlContext));
        }

        return $output;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setRequired(array(
            'class'
        ));

        $resolver->setDefaults(array(
            'args' => null,

        ));
    }

    public function getName()
    {
        return 'transformer';
    }

    public function getETLType()
    {
        return 'transformer';
    }
}