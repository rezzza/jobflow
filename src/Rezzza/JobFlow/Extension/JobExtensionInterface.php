<?php

namespace Rezzza\JobFlow\Extension;

/**
 * Extensions let add functionality to all type
 *
 * @author Timothée Barray <tim@amicalement-web.net>
 */
interface JobExtensionInterface
{
    public function getType($name);

    public function hasType($name);

    public function getWrapper($name);

    public function hasWrapper($name);
}