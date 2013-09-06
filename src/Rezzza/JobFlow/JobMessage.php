<?php

namespace Rezzza\JobFlow;

class JobMessage
{
    public $context;

    public $data = array();

    public $input;

    public function __construct($context)
    {
        $this->context = $context;
    }

    public function hasData()
    {
        return count($this->data) > 0;
    }

    public function getData()
    {
        return $this->data;
    }

    public function setData($data)
    {
        $this->data = $data;
    }

    public function hasInput()
    {
        return null !== $this->input;
    }

    public function getInput()
    {
        return $this->input;
    }

    public function setInput($input)
    {
        $this->input = $input;
    }

    public function __clone()
    {
        $this->context = clone $this->context;
    }
}