<?php

namespace Rezzza\Jobflow;

class JobMessage
{
    public $context;

    public $input;

    public $output;

    public $pipe;

    public $metadata;

    public $jobOptions = array();

    public function __construct($context)
    {
        $this->context = $context;
    }

    public function __clone()
    {
        $this->context = clone $this->context;
    }

    /**
     * Creates new message from an existing by moving output to input
     */
    public function reset()
    {
        $msg = clone $this;
        $msg->input = $msg->output;
        $msg->output = null;

        return $msg;
    }
}