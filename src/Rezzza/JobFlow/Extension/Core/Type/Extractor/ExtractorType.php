<?php

namespace Rezzza\JobFlow\Extension\Core\Type\Extractor;

use Knp\ETL;

use Rezzza\JobFlow\Extension\Core\Type\ETLType;
use Rezzza\JobFlow\Scheduler\ExecutionContext;

class ExtractorType extends ETLType
{
    public function execute($input, ExecutionContext $execution)
    {
        if ($this->isLoggable($input)) {
            $input->setLogger($execution->getLogger());
        }

        $execution->setGlobalOption('total', $input->count());

        $offset = $execution->getOption('offset');
        $limit = $execution->getOption('limit');
        $results = array();

        $input->seek($offset);
        $etl = new ETL\Context\Context();
        
        // Skip Header
        if ($offset === 0) {
            $input->extract($etl);
        }

        for ($i = 0; $i < $limit && $input->valid(); $i++) {
            $results[] = $input->current();
            $input->next();
        }

        return $results;
    }

    public function getName()
    {
        return 'extractor';
    }

    public function getETLType()
    {
        return 'extractor';
    }
}