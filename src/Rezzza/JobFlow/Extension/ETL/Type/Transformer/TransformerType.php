<?php

namespace Rezzza\JobFlow\Extension\ETL\Type\Transformer;

use Knp\ETL;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Rezzza\JobFlow\Extension\ETL\Type\ETLType;
use Rezzza\JobFlow\JobBuilder;
use Rezzza\JobFlow\JobInput;
use Rezzza\JobFlow\JobOutput;
use Rezzza\JobFlow\Scheduler\ExecutionContext;

class TransformerType extends ETLType
{
    protected $transformer;

    protected $etlContext;

    protected $updateMethod;

    protected $transformClass;

    public function buildJob(JobBuilder $builder, array $options)
    {
        $this->transformer = $options['etl_config']['transformer'];
        $this->etlContext = new ETL\Context\Context();
        $this->updateMethod = $options['update_method'];
        $this->transformClass = $options['transform_class'];
    }

    public function execute(JobInput $input, JobOutput $output, ExecutionContext $execution)
    {
        foreach ($input->source as $k => $result) {
            if ($execution->getLogger()) {
                $execution->getLogger()->debug('transformation '.$k);
            }

            $this->etlContext->setTransformedData(new $this->transformClass);

            $transformedData = $this->transformer->transform($result, $this->etlContext);

            if ($this->updateMethod) {
                call_user_func($this->updateMethod, $transformedData);
            }
            
            $output->write($transformedData);
        }

        return $output;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setRequired(array(
            'class'
        ));

        $resolver->setDefaults(array(
            'transform_class' => null,
            'update_method' => null,
            'etl_config' => function(Options $options) {
                $class = $options['class'];

                return array(
                    'transformer' => new $class()
                );
            } 
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