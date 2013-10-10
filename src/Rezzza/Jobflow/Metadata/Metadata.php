<?php

namespace Rezzza\Jobflow\Metadata;

class Metadata implements \ArrayAccess
{
    protected $name;

    protected $values = array();

    public function __construct($name, array $values = array())
    {
        $this->name = $name;
        $this->values = $values;
    }

    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->values);
    }

    public function offsetGet($offset)
    {
        return $this->offsetExists($offset) ? $this->values[$offset] : null;
    }

    public function offsetSet($offset, $value)
    {
        $this->values[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        unset($this->values[$offset]);
    }
}