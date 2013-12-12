<?php

namespace Rezzza\Jobflow\Io;

class IoDescriptor
{
    protected $stdin;

    protected $stdout;

    protected $stderr;

    public function __construct(Input $stdin = null, Output $stdout = null, $stderr = null)
    {
        $this->stdin = $stdin;
        $this->stdout = $stdout;
        $this->stderr = $stderr;
    }

    public function getStdin()
    {
        return $this->stdin;
    }

    public function getStdout()
    {
        return $this->stdout;
    }
}
