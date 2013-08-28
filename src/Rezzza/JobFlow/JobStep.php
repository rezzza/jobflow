<?php

namespace Rezzza\JobFlow;

/**
 * @author Timothée Barray <tim@amicalement-web.net>
 */
class JobStep
{
    public $output;

    public function __construct($stdout)
    {
        $this->output = $stdout->getDsn();
    }
}