<?php

namespace Rezzza\Jobflow\Extension\ETL\Context;

use Knp\ETL\Context\Context;

class ETLProcessorContext extends Context
{
    public $metadata;

    public $execution;

    public function __construct($execution, $metadata, $id = null)
    {
        $this->execution = $execution;
        $this->metaata = $metadata;

        parent::__construct($id);
    }
}
