<?php

namespace Rezzza\JobFlow\Io;

abstract class AbstractStream
{
    protected $dsn;

    protected $fileInfo;

    protected $resolved = false;

    protected $wrapper;

    protected $format;

    public $parts = array();

    public function __construct($dsn, $wrapper = null, $format = null)
    {
        $this->fileInfo = new \SplFileInfo($dsn);
        $this->dsn = $dsn;
        $this->wrapper = $wrapper;
        $this->format = $format ?: $this->fileInfo->getExtension();
    }

    public function getDsn()
    {
        return $this->dsn;
    }

    public function getFormat()
    {
        return $this->format;
    }

    public function getWrapper()
    {
        if (null === $this->wrapper) {
            throw new \RuntimeException(sprintf('No wrapper defined for stream with dsn : "%s"', $this->dsn));
        }

        return $this->wrapper;
    }

    public function setWrapper($wrapper)
    {
        $this->wrapper = $wrapper;
        $this->resolved = true;
    }

    public function isConnectedTo($name)
    {
        // path get the started slash, so substr is needed
        return substr($this->parts['path'], 1) === $name;
    }
}