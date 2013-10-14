<?php

namespace Rezzza\Jobflow\Event;

/**
 * @author Timothée Barray <tim@amicalement-web.net>
 */
final class JobEvents
{
    const PRE_CONFIG = 'job.pre_config';

    const POST_CONFIG = 'job.post_config';

    const EXECUTE = 'job.execute';

    const PRE_EXECUTE = 'job.pre_execute';

    const POST_EXECUTE = 'job.post_execute';

    private function __construct()
    {
    }
}
