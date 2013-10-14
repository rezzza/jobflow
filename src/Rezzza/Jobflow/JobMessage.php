<?php

namespace Rezzza\Jobflow;

class JobMessage
{
    public $context;

    public $data = array();

    public $pipe = array();

    public $metadata;

    public $jobOptions = array();

    public $ended = false;

    public function __construct($context)
    {
        $this->context = $context;
    }

    public function __clone()
    {
        $this->context = clone $this->context;
    }

    public function reset()
    {
        $this->data = array();
        $this->pipe = array();
    }

    public function getMetadata($name, $offset = 0)
    {
        $offset = $offset + $this->context->getOption('offset');

        return $this->metadata[$name][$offset];
    }
}