<?php

namespace Rezzza\JobFlow;

class JobMessage
{
    public $context;

    public $input;

    public $output;

    public $pipe;

    public function __construct($context)
    {
        $this->context = $context;
    }

    public function __clone()
    {
        $this->context = clone $this->context;
    }
}