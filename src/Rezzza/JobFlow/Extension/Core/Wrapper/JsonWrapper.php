<?php

namespace Rezzza\JobFlow\Extension\Core\Wrapper;

use Knp\ETL;

use Rezzza\JobFlow\Io\IoWrapperInterface;

class JsonWrapper implements IoWrapperInterface
{
    public function getOutput($path)
    {
        return new ETL\Loader\FileLoader($path, 'w+');
    }

    public function getName()
    {
        return 'json';
    }
}