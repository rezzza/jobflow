<?php

namespace Rezzza\Jobflow\Tests\Units\Plugin\SymfonyBundle\DependencyInjection;

use mageekguy\atoum as Units;

use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Yaml\Yaml;

use Rezzza\Jobflow\Plugin\SymfonyBundle\DependencyInjection\Configuration as TestedClass;

class Configuration extends Units\Test
{
    public function test_config_is_valid()
    {
        $this
            ->if($config = Yaml::parse(file_get_contents(__DIR__.'/Fixtures/rabbit.yml')))
            ->and($configuration = new TestedClass())
            ->and($treeBuilder = $configuration->getConfigTreeBuilder())
            ->and($processor = new Processor)
            ->then($config = $processor->process($treeBuilder->buildTree(), $config))
                ->variable($config)
                    ->isEqualTo($this->getConfigOutput())
        ;
    }

    private function getConfigOutput()
    {
        return array(
            'transports' => array(
                'rabbitmq' => array(
                    'connections' => array(
                        'jobflow' => array(
                            "host" => 'localhost',
                            "port" => 5672,
                            "user" => 'guest',
                            "password" =>'guest',
                            "vhost" => '/'
                        )
                    )
                )
            )
        );
    }
}