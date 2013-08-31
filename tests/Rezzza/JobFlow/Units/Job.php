<?php

namespace Rezzza\JobFlow\Tests\Units;

use mageekguy\atoum as Units;

use Rezzza\JobFlow\Job as TestedClass;

class Job extends Units\Test
{
    public function test_should_have_a_child()
    {
        $this->mockGenerator->orphanize('__construct');

        $this
            ->if($mockConfig = new \mock\Rezzza\JobFlow\JobConfig)
            ->and($job = new TestedClass($mockConfig))
            ->and($child = new TestedClass($mockConfig))
            ->and($mockConfig->getMockController()->getName = 'child')
            ->then($job->add($child))
                ->object($job->get('child'))->isIdenticalTo($child)
        ;
    }

    public function test_should_throw_exception_on_incorrect_name()
    {
        $this->mockGenerator->orphanize('__construct');

        $this
            ->if($mockConfig = new \mock\Rezzza\JobFlow\JobConfig)
            ->and($mockConfig->getMockController()->getName = 'test')
            ->then($job = new TestedClass($mockConfig))
                ->exception(function() use ($job) {
                    $job->get('test');
                })
                ->hasMessage('No child with name : "test" in job "test"')
        ;
    }
}
