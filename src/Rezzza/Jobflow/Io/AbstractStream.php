<?php

namespace Rezzza\Jobflow\Io;

abstract class AbstractStream
{
    protected $dsn;

    protected $fileInfo;

    public function __construct($dsn)
    {
        $this->initFileInfo($dsn);
        $this->dsn = $dsn;
    }

    public function initFileInfo($dsn)
    {
        $this->fileInfo = new \SplFileInfo($dsn);
    }

    public function getDsn()
    {
        return $this->dsn;
    }

    public function __sleep()
    {
        return array('dsn');
    }

    public function _wakeup()
    {
        $this->initFileInfo($this->dsn);
    }
}