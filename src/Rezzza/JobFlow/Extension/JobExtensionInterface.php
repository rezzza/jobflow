<?php

namespace Rezzza\JobFlow\Extension;

/**
 * @author Timothée Barray <tim@amicalement-web.net>
 */
interface JobExtensionInterface
{
    public function getType($name);

    public function hasType($name);

    public function getWrapper($name);

    public function hasWrapper($name);
}