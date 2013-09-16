<?php

namespace Rezzza\Jobflow;

use Rezzza\Jobflow\Scheduler\ExecutionContext;

/**
 * @author Timothée Barray <tim@amicalement-web.net>
 */
interface JobInterface
{
    public function execute(ExecutionContext $execution);
}