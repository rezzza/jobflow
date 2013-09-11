<?php

namespace Rezzza\JobFlow;

/**
 * Allow to pass a dynamic args which will be executed only when needed.
 * Without, cannot differentiate Closure required as args, and Args wanted to be executed. 
 */
class DelayedArg
{
    private $closure;

    public function __construct(\Closure $closure)
    {
        $this->closure = $closure;
    }

    public function __invoke()
    {
        return call_user_func($this->closure);
    }
}