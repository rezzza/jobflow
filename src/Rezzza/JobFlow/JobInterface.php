<?php

namespace Rezzza\JobFlow;

use Rezzza\JobFlow\Scheduler\ExecutionContext;

/**
 * @author Timothée Barray <tim@amicalement-web.net>
 */
interface JobInterface
{
    public function execute(ExecutionContext $execution);
}