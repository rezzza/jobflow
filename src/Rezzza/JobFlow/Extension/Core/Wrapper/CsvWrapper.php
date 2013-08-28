<?php

namespace Rezzza\JobFlow\Extension\Core\Wrapper;

use Knp\ETL;

use Rezzza\JobFlow\Io\IoWrapperInterface;

class CsvWrapper implements IoWrapperInterface
{
    public $delimiter = ";";

    public function read($path)
    {
        return new ETL\Extractor\CsvExtractor($path, $this->delimiter);
    }

    public function getName()
    {
        return 'csv';
    }
}