<?php

namespace Rezzza\Jobflow\Scheduler;

use ArrayIterator;

/**
 * @author Timothée Barray <tim@amicalement-web.net>
 */
class JobGraph implements \IteratorAggregate, \Countable
{
    private $graph;

    public function __construct(ArrayIterator $graph)
    {
        $this->graph = $graph;
    }

    public function current()
    {
        return $this->getIterator()->current();
    }

    public function getArrayCopy()
    {
        return $this->getIterator()->getArrayCopy();
    }

    public function seek($index)
    {
        return $this->getIterator()->seek($index);
    }

    public function key()
    {
        return $this->getIterator()->key();
    }

    public function next()
    {
        return $this->getIterator()->next();
    }

    public function rewind()
    {
        return $this->getIterator()->rewind();
    }

    public function search($value)
    {
       return array_search($value, $this->getArrayCopy());
    }

    /**
     * Moves cursor to the given value.
     * Useful when using asynchronous transport
     */
    public function move($value)
    {
        // No need to update if $value is already current one
        if ($value === $this->current()) {
            return;
        }

        $index = $this->search($value);

        if (false === $index) {
            throw new \InvalidArgumentException(sprintf('"%s" value not found in JobGraph', $value));
        }

        return $this->seek($index);
    }

    /**
     * Ensure we have one more job next
     *
     * @return boolean
     */
    public function hasNextJob()
    {
        return $this->getIterator()->offsetExists($this->graph->key() + 1);
    }

    /**
     * Get name of the job for given index
     *
     * @param string|integer $index
     *
     * @return string
     */
    public function getJob($index)
    {
        return $this->getIterator()->offsetGet($index);
    }

    /**
     * Get name of the next child job
     *
     * @return string
     */
    public function getNextJob()
    {
        return $this->getJob($this->graph->key() + 1);
    }

    /**
     * Get name of the previous child job
     *
     * @return string
     */
    public function getPreviousJob()
    {
        return $this->getJob($this->graph->key() - 1);
    }

    public function isLast($value)
    {
        return ($this->count() -1) === $this->search($value);
    }

    /**
     * @return Iterator
     */
    public function getIterator()
    {
        return $this->graph;
    }

    public function count()
    {
        return $this->getIterator()->count();
    }
}
