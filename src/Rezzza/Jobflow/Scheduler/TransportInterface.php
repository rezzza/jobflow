<?php

namespace Rezzza\Jobflow\Scheduler;

use Rezzza\Jobflow\JobMessage;

interface TransportInterface
{
    /**
     * @return \Rezzza\Jobflow\Extension\Core\Transport\PhpTransport|null
     */
    public function addMessage(JobMessage $msg);

    public function getMessage();

    /**
     * @return string
     */
    public function getName();
}