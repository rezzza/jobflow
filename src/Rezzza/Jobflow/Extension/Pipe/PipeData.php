<?php

namespace Rezzza\Jobflow\Extension\Pipe;

use Rezzza\Jobflow\JobData;

class PipeData extends JobData
{
    public function isPiped()
    {
        return true;
    }
}