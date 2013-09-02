<?php

namespace Rezzza\JobFlow\Scheduler;

interface TransportInterface
{
    public function addMessage($msg, $name = null);

    public function getMessage();

    public function getName();
}