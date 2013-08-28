<?php

namespace Rezzza\JobFlow;

/**
 * @author Timothée Barray <tim@amicalement-web.net>
 */
interface JobInterface
{
    public function execute($input, $execution);
}