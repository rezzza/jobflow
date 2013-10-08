<?php

namespace Rezzza\Jobflow\Extension\ETL\Type\Transformer;

use Knp\ETL;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Rezzza\Jobflow\Extension\ETL\Type\ETLType;
use Rezzza\Jobflow\JobBuilder;
use Rezzza\Jobflow\JobInput;
use Rezzza\Jobflow\JobOutput;
use Rezzza\Jobflow\Scheduler\ExecutionContext;

class TransformerType extends ETLType
{
    protected $etlContext;

    protected $updateMethod;

    protected $transformClass;

    public function buildConfig($config, $options)
    {
        parent::buildConfig($config, $options);

        $this->etlContext = new ETL\Context\Context();
        $this->updateMethod = $options['update_method'];
        $this->transformClass = $options['transform_class'];
    }

    public function execute(JobInput $input, JobOutput $output, ExecutionContext $execution)
    {       
        $transformer = $input->getProcessor();

        foreach ($input->getData() as $k => $result) {
            $output->writeMetadata($result, $k);

            if ($this->transformClass) {
                $this->etlContext->setTransformedData(new $this->transformClass);
            }

            $transformedData = $transformer->transform($result, $this->etlContext);

            if ($this->updateMethod) {
                call_user_func($this->updateMethod, $transformedData);
            }

            if ($execution->getLogger()) {
                $execution->getLogger()->debug('transformation '.$k.' : '.json_encode($transformedData));
            }
            
            $output->write($transformedData, $k);
        }

        return $output;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        parent::setDefaultOptions($resolver);

        $resolver->setDefaults(array(
            'transform_class' => null,
            'update_method' => null
        ));
    }

    public function getName()
    {
        return 'transformer';
    }

    public function getETLType()
    {
        return self::TYPE_TRANSFORMER;
    }
}