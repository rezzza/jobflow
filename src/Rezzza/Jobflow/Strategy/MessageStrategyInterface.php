<?php

namespace Rezzza\Jobflow\Strategy;

use Rezzza\Jobflow\JobMessage;

interface MessageStrategyInterface
{
    public function handle(JobMessage $msg);
}
