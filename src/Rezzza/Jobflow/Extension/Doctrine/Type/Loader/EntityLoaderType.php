<?php

namespace Rezzza\Jobflow\Extension\Doctrine\Type\Loader;

use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Rezzza\Jobflow\AbstractJobType;

class EntityLoaderType extends AbstractJobType
{
    private $doctrine;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $doctrine = $this->doctrine;

        $resolver->setDefaults(array(
            'class' => 'Knp\ETL\Loader\Doctrine\ORMLoader',
            'etl_config' => function(Options $options) use ($doctrine) {
                $class = $options['class'];

                return array(
                    'class' => $class,
                    'args' => array($doctrine)
                );
            } 
        ));
    }

    public function getName()
    {
        return 'entity_loader';
    }

    public function getParent()
    {
        return 'loader';
    }
}