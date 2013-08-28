<?php

namespace Rezzza\JobFlow\Extension\Core\Wrapper;

use Rezzza\JobFlow\Io\IoWrapperInterface;

class JobWrapper implements IoWrapperInterface
{
    public function getName()
    {
        return 'job';
    }
}