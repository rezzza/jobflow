<?php

namespace Rezzza\JobFlow\Scheduler;

use ArrayIterator;

/**
 * @author Timothée Barray <tim@amicalement-web.net>
 */
class JobGraph implements \IteratorAggregate
{
    public $graph;

    public function __construct(ArrayIterator $graph)
    {
        $this->graph = $graph;
    }

    public function current()
    {
        return $this->graph->current();
    }

    public function getArrayCopy()
    {
        return $this->graph->getArrayCopy();
    }

    public function seek($index)
    {
        return $this->graph->seek($index);
    }

    /**
     * @return boolean
     */
    public function hasNextJob()
    {
        return $this->graph->offsetExists($this->graph->key() + 1);
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
     * Get name of the job for given index
     *
     * @param string|integer $index
     *
     * @return string
     */
    public function getJob($index)
    {
        return $this->graph->offsetGet($index);
    }

    /**
     * @return Iterator
     */
    public function getIterator()
    {
        return $this->graph;
    }
}