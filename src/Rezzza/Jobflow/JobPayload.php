<?php

namespace Rezzza\Jobflow;

class JobPayload implements \ArrayAccess, \IteratorAggregate, \Countable
{
    /**
     * JobData[]
     */
    public $datas;

    public function __construct(array $datas = [])
    {
        $this->datas = $datas;
    }

    public function store($data)
    {
        $this->datas[] = $data;
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->datas);
    }

    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->datas);
    }

    public function offsetGet($offset)
    {
        return $this->datas[$offset];
    }

    public function offsetSet($offset, $value)
    {
        $this->datas[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        unset($this->datas[$offset]);
    }

    public function count()
    {
        return count($this->datas);
    }
}