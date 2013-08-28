<?php

namespace Rezzza\JobFlow\Extension\Core\Type\Loader;

use Knp\ETL;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Rezzza\JobFlow\Extension\Core\Type\ETLType;
use Rezzza\JobFlow\JobBuilder;
use Rezzza\JobFlow\Scheduler\ExecutionContext;

class LoaderType extends ETLType
{
    public function buildJob(JobBuilder $builder, array $options)
    {
        $this->loader = $options['loader'];
    }

    public function execute($input, ExecutionContext $execution)
    {
        if ($this->isLoggable($this->loader)) {
            $this->loader->setLogger($execution->getLogger());
        }

        foreach ($input as $d) {
            $this->loader->load($d, new ETL\Context\Context());
        }

        return []; // End chain should return empty array
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $type = $this;

        $resolver->setDefaults(array(
            'class' => 'Knp\ETL\Loader\FileLoader',
            'loader' => function(Options $options) use ($type) { 
                $class = $options['class'];

                return new $class($options['io']->stdout->getDsn());
            } 
        ));
    }

    public function getName()
    {
        return 'loader';
    }

    public function getETLType()
    {
        return 'loader';
    }
}