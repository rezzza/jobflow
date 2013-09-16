<?php

namespace Rezzza\Jobflow\Scheduler;

use Rezzza\Jobflow\JobMessage;

interface TransportInterface
{
    public function addMessage(JobMessage $msg);

    public function getMessage();

    public function getName();
}