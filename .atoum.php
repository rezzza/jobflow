<?php
use mageekguy\atoum;

$runner->addTestsFromDirectory(__DIR__.'/tests/units/');

$script
    ->addDefaultReport()
        ->addField(new atoum\report\fields\runner\result\logo())
        ->addField(new atoum\report\fields\runner\coverage\html(
                'Code coverage',
                __DIR__.'/web/code-coverage'
            )
        )
;

$script->noCodeCoverageForNamespaces('mageekguy', 'Symfony');
$script->bootstrapFile(__DIR__ . DIRECTORY_SEPARATOR . '.atoum.bootstrap.php');
