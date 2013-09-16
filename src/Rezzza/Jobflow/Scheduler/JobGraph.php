<?php

namespace Rezzza\Jobflow\Scheduler;

use ArrayIterator;

/**
 * @author Timothée Barray <tim@amicalement-web.net>
 */
class JobGraph implements \IteratorAggregate, \Countable
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

    public function key()
    {
        return $this->graph->key();
    }

    public function search($value)
    {
       return array_search($value, $this->getArrayCopy());
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
     * Get the extractor linked to the index
     */
    public function getExtractor($index)
    {
        if ($this->isLoader($index)) {
            $result = $index - 2;
        } elseif ($this->isTransformer($index)) {
            $result = $index - 1;
        } else {
            $result = $index;
        }

        return $this->getJob($result);
    }

    /**
     * Get the transformer linked to the index
     */
    public function getTransformer($index)
    {
        if ($this->isLoader($index)) {
            return $index - 1;
        } elseif ($this->isExtractor($index)) {
            return $index + 1;
        } else {
            return $index;
        }
    }

    /**
     * Get the loader linked to the index
     */
    public function getLoader($index)
    {
        if ($this->isExtractor($index)) {
            return $index + 2;
        } elseif ($this->isTransformer($index)) {
            return $index + 1;
        } else {
            return $index;
        }
    }

    /**
     * Checks index is the extractor
     */
    public function isExtractor($index)
    {
        return $index % 3 === 0;
    }

    /**
     * Checks index is the transformer
     */
    public function isTransformer($index)
    {
        return $index % 3 === 1;
    }

    /**
     * Checks index is the loader
     */
    public function isLoader($index)
    {
        return $index % 3 === 2;
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