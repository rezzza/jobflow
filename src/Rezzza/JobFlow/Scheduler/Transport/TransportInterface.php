<?php

namespace Rezzza\JobFlow\Scheduler\Transport;

interface TransportInterface
{
    public function addMessage($msg, $name = null);

    public function getMessage();
}