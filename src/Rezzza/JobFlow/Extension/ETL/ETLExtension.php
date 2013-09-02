<?php

namespace Rezzza\JobFlow\Extension\ETL;

use Rezzza\JobFlow\Extension\BaseExtension;

class ETLExtension extends BaseExtension
{
    public function loadTypes()
    {
        return array(
            new Type\Extractor\ExtractorType(),
            new Type\Transformer\TransformerType(),
            new Type\Transformer\CallbackTransformerType(),
            new Type\Loader\LoaderType(),
            new Type\Loader\FileLoaderType()
        );
    }
}