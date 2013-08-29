<?php

namespace Rezzza\JobFlow\Extension\Core\Type\Transformer;

use Knp\ETL;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Rezzza\JobFlow\Extension\Core\Type\ETLType;
use Rezzza\JobFlow\JobBuilder;
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

    public function execute($input, ExecutionContext $execution)
    {
        $results = [];

        foreach ($input as $k => $result) {
            $execution->getLogger()->debug('transformation '.$k);
            $etlContext = new ETL\Context\Context();

            $results[] = $this->transformer->transform($result, $etlContext);
        }

        return $results;
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