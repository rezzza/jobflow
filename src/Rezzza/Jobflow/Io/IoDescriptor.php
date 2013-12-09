<?php

namespace Rezzza\Jobflow\Io;

class IoDescriptor
{
    public $stdin;

    public $stdout;

    public $stderr;

    public function __construct(Input $stdin = null, Output $stdout = null, $stderr = null)
    {
        $this->stdin = $stdin;
        $this->stdout = $stdout;
        $this->stderr = $stderr;
    }
}
