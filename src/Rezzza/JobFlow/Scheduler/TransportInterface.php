<?php

namespace Rezzza\JobFlow\Scheduler;

use Rezzza\JobFlow\JobMessage;

interface TransportInterface
{
    public function addMessage(JobMessage $msg);

    public function getMessage();

    public function getName();
}