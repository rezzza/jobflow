<?php

namespace Rezzza\JobFlow\Io;

/**
 * @author Timothée Barray <tim@amicalement-web.net>
 */
class Input extends AbstractStream
{
    public function getWrapper($etl)
    {
        return $etl['extractor'];
    }
}