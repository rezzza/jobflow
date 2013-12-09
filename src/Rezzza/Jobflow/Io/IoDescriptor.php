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
        if (null === $this->stdin) {
            throw new \InvalidArgumentException('No stdin defined');
        }

        return $this->stdin;
    }

    public function getStdout()
    {
        if (null === $this->stdout) {
            throw new \InvalidArgumentException('No stdout defined');
        }

        return $this->stdout;
    }
}
