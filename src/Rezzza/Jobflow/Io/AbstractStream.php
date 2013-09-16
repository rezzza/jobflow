<?php

namespace Rezzza\Jobflow\Io;

abstract class AbstractStream
{
    protected $dsn;

    protected $fileInfo;

    public function __construct($dsn)
    {
        $this->fileInfo = new \SplFileInfo($dsn);
        $this->dsn = $dsn;
    }

    public function getDsn()
    {
        return $this->dsn;
    }
}