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

    public function setExecOptions(OptionsResolverInterface $resolver)
    {
        $doctrine = $this->doctrine;

        $resolver->setDefaults(array(
            'flush_every' => 50,
            'class' => 'Knp\ETL\Loader\Doctrine\ORMLoader',
            'args' => function(Options $options) use ($doctrine) {
                return array(
                    $doctrine,
                    $options['flush_every']
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