<?php

namespace Rezzza\JobFlow\Extension\Core\Wrapper;

class TsvWrapper extends CsvWrapper
{
    public $delimiter = "\t";

    public function getName()
    {
        return 'tsv';
    }
}