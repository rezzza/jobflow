<?php

namespace Rezzza\Jobflow;

use Rezzza\Jobflow\Metadata\Metadata;

class JobData
{
    protected $value;

    protected $metadata;

    public function __construct($value, Metadata $metadata)
    {
        $this->value = $value;
        $this->metadata = $metadata;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function getMetadata()
    {
        return $this->metadata;
    }

    public function isPiped()
    {
        return false;
    }
}
