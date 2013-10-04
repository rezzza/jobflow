<?php

namespace Rezzza\Jobflow\Tests\Units;

use mageekguy\atoum as Units;

use Rezzza\Jobflow\JobBuilder as TestedClass;

class JobBuilder extends Units\Test
{
    private $factory;

    private $builder;

    public function beforeTestMethod($method)
    {
        $this->mockGenerator->orphanize('__construct');
        $registry = new \mock\Rezzza\Jobflow\JobRegistry;
        $this->factory = new \mock\Rezzza\Jobflow\JobFactory($registry);
        $this->builder = new TestedClass('name', $this->factory);
    }

    public function test_name_should_be_string_or_int()
    {
        $builder = $this->builder;

        $this
            ->exception(function() use ($builder) {
                $builder->add(true, 'test');
            })
                ->hasMessage('child name should be string or, integer')
        ;
    }

    public function test_type_should_be_string_or_jobtype()
    {
        $builder = $this->builder;

        $this
            ->exception(function() use ($builder) {
                $builder->add('child', 123);
            })
                ->hasMessage('type should be string or JobTypeinterface')
        ;
    }

    public function test_it_is_fluent()
    {
        $this
            ->if($builder = $this->builder->add('foo', 'text', array('bar' => 'baz')))
                ->object($builder)
                    ->isIdenticalTo($this->builder)
        ;
    }

    public function test_it_adds_child_with_string_name()
    {
        $this
            ->boolean($this->builder->has('foo'))
                ->isFalse()
            
            ->then($this->builder->add('foo', 'text', array('bar' => 'baz')))
                ->boolean($this->builder->has('foo'))
                    ->isTrue()
        ;
    }

    public function test_it_adds_child_with_int_name()
    {
        $this
            ->boolean($this->builder->has(123))
                ->isFalse()

            ->then($builder = $this->builder->add(123, 'text', array('bar' => 'baz')))
                ->boolean($builder->has(123))
                    ->isTrue()
        ;
    }

    public function test_it_adds_child_with_jobtype()
    {
        $this
            ->boolean($this->builder->has(123))
                ->isFalse()

            ->if($mockType = new \mock\Rezzza\Jobflow\Extension\Core\Type\JobType)
            ->then($builder = $this->builder->add(123, $mockType, array('bar' => 'baz')))
                ->boolean($builder->has(123))
                    ->isTrue()
        ;
    }

    public function test_create_method_use_factory()
    {
        $this
            ->if($this->factory->getMockController()->createNamedBuilder = true)
            ->then($this->builder->create('flex', null))
                ->mock($this->factory)
                    ->call('createNamedBuilder')
                        ->withArguments('flex', 'job', null, array())
                        ->once()
        ;
    }

    public function test_it_returns_a_job()
    {
        $this
            ->if($mockType = new \mock\Rezzza\Jobflow\Extension\Core\Type\JobType)
            ->and($mockBuilder = new \mock\Rezzza\Jobflow\JobBuilder('name', $this->factory))
            ->and($mockBuilder->add(123, $mockType))

                ->boolean($mockBuilder->hasUnresolvedChildren())
                    ->isTrue()

            ->if($job = $mockBuilder->getJob())
                ->object($job)
                    ->isInstanceOf('Rezzza\Jobflow\Job')

                ->mock($mockBuilder)
                    ->call('create')
                        ->withArguments(123, $mockType, array())
                        ->once()

                ->boolean($mockBuilder->hasUnresolvedChildren())
                    ->isFalse()
        ;
    }
}