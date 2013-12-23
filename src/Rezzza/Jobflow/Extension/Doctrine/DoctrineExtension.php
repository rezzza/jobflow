<?php

namespace Rezzza\Jobflow\Extension\Doctrine\Type\Loader;

use Doctrine\Common\Persistence\ManagerRegistry;

use Rezzza\Jobflow\Extension\BaseExtension;
use Rezzza\Jobflow\Extension\Doctrine\Type;

class DoctrineExtension extends BaseExtension
{
    private $doctrine;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    public function loadTypes()
    {
        return array(
            new Type\Loader\EntityLoaderType($this->doctrine)
        );
    }
}