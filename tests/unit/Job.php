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
            ->if($mockResolved = new \mock\Rezzza\JobFlow\ResolvedJob)
            ->and($job = new TestedClass('test', $mockResolved))
            ->and($child = new TestedClass('child', $mockResolved))
            ->then($job->add($child))
                ->object($job->get('child'))->isIdenticalTo($child)
        ;
    }

    public function test_should_throw_exception_on_incorrect_name()
    {
        $this->mockGenerator->orphanize('__construct');

        $this
            ->if($mockResolved = new \mock\Rezzza\JobFlow\ResolvedJob)
            ->then($job = new TestedClass('test', $mockResolved))
                ->exception(function() use ($job) {
                    $job->get('test');
                })
                ->hasMessage('No child with name : "test" in job "test"')
        ;
    }
}
