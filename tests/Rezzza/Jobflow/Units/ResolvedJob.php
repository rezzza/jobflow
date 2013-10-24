<?php

namespace Rezzza\Jobflow\Tests\Units;

use mageekguy\atoum as Units;

use Rezzza\Jobflow\ResolvedJob as TestedClass;

class ResolvedJob extends Units\Test
{
    private $jobType;

    private $parentType;

    private $resolvedParent;

    public function beforeTestMethod($method)
    {
        $this->jobType = $this->getMockJobType(
            array('a' => 'jean', 'b' => 'marc'),
            array('aa' => 'jeanjean')
        );

        $this->parentType = $this->getMockJobType(
            array('c' => 'john', 'd' => 'doe'),
            array('bb' => 'jojo', 'cc' => 'dodo')
        );

        $this->resolvedParent = new TestedClass($this->parentType);
    }

    public function test_it_should_set_init_options_from_type()
    {
        $this
            ->given(
                $resolved = new TestedClass($this->jobType)
            )
            ->then(
                $resolver = $resolved->getInitOptionsResolver(),
                $options = $resolver->resolve(array('a' => 'paul'))
            )
                ->array($options)
                    ->isEqualTo(array('a' => 'paul', 'b' => 'marc'))
        ;
    }

    public function test_it_should_set_exec_options_from_type()
    {
        $this
            ->given(
                $resolved = new TestedClass($this->jobType)
            )
            ->then(
                $resolver = $resolved->getExecOptionsResolver(),
                $options = $resolver->resolve(array('aa' => 'paul'))
            )
                ->array($options)
                    ->isEqualTo(array('aa' => 'paul'))
        ;
    }

    public function test_it_should_set_init_options_from_type_and_parent()
    {
        $this
            ->given(
                $resolved = new TestedClass($this->jobType, array(), $this->resolvedParent)
            )
            ->then(
                $resolver = $resolved->getInitOptionsResolver(),
                $options = $resolver->resolve(array('a' => 'paul', 'c' => 'flex'))
            )
                ->array($options)
                    ->isEqualTo(array(
                        'a' => 'paul', 
                        'b' => 'marc', 
                        'c' => 'flex', 
                        'd' => 'doe'
                    ))
        ;
    }

    public function test_it_should_set_exec_options_from_type_and_parent()
    {
        $this
            ->given(
                $resolved = new TestedClass($this->jobType, array(), $this->resolvedParent)
            )
            ->then(
                $resolver = $resolved->getExecOptionsResolver(),
                $options = $resolver->resolve(array('bb' => 'gogo'))
            )
                ->array($options)
                    ->isEqualTo(array(
                        'aa' => 'jeanjean',
                        'bb' => 'gogo', 
                        'cc' => 'dodo'
                    ))
        ;
    }

    public function test_it_should_create_a_builder()
    {
        $this
            ->given(
                $factory = $this->getMockJobFactory(),
                $givenOptions = array('a' => 'john', 'b' => 'doe'),
                $resolvedOptions = array('a' => 'jean', 'b' => 'marc', 'c' => 'bob'),
                $mockResolver = new \mock\Symfony\Component\OptionsResolver\OptionsResolverInterface,
                $mockResolver->getMockController()->resolve = $resolvedOptions,
                $resolvedJob = new \mock\Rezzza\Jobflow\ResolvedJob($this->jobType, array(), $this->resolvedParent),
                $resolvedJob->getMockController()->getInitOptionsResolver = $mockResolver
            )
            ->then(
                $builder = $resolvedJob->createBuilder('name', $factory, $givenOptions)
            )
                ->object($builder)
                    ->isInstanceOf('Rezzza\Jobflow\JobBuilder')

                ->object($builder->getResolved())
                    ->isIdenticalTo($resolvedJob)

                ->mock($mockResolver)
                    ->call('resolve')
                        ->once()

                ->mock($resolvedJob)
                    ->call('getInitOptionsResolver')
                        ->once()

                ->array($builder->getInitOptions())
                    ->isEqualTo(array(
                        'a' => 'jean', 
                        'b' => 'marc', 
                        'c' => 'bob'
                    ))
        ;
    }

    public function test_it_should_build_a_job()
    {
        $this
            ->given(
                $givenOptions = array('a' => 'john', 'b' => 'doe'),
                $expectedOptions = array(
                    'a' => 'john', 
                    'b' => 'doe',
                    'c' => 'john', 
                    'd' => 'doe'
                ),
                $factory = $this->getMockJobFactory(),
                $resolvedJob = new \mock\Rezzza\Jobflow\ResolvedJob($this->jobType, array(), $this->resolvedParent)
            )
            ->then(
                $builder = $resolvedJob->createBuilder('name', $factory, $givenOptions)
            )
            ->mock($this->jobType)
                ->call('buildJob')
                    ->withArguments($builder, $expectedOptions)
                    ->once()

            ->mock($this->parentType)
                ->call('buildJob')
                    ->withArguments($builder, $expectedOptions)
                    ->once()
        ;
    }

    public function test_it_should_build_exec_for_a_job()
    {
        $this
            ->given(
                $factory = $this->getMockJobFactory(),
                $ed = new \mock\Symfony\Component\EventDispatcher\EventDispatcherInterface,
                $config = new \mock\Rezzza\Jobflow\JobConfig('name', $ed),
                $givenOptions = array('a' => 'john', 'b' => 'doe'),
                $resolvedOptions = array('a' => 'jean', 'b' => 'marc', 'c' => 'bob'),
                $mockResolver = new \mock\Symfony\Component\OptionsResolver\OptionsResolverInterface,
                $mockResolver->getMockController()->resolve = $resolvedOptions,
                $resolvedJob = new \mock\Rezzza\Jobflow\ResolvedJob($this->jobType, array(), $this->resolvedParent),
                $resolvedJob->getMockController()->getExecOptionsResolver = $mockResolver
            )
            ->then(
                $options = $resolvedJob->execJob($config, $givenOptions)
            )
                ->array($options)
                    ->isEqualTo($resolvedOptions)

                ->mock($mockResolver)
                    ->call('resolve')
                        ->once()

                ->mock($resolvedJob)
                    ->call('getExecOptionsResolver')
                        ->once()
        ;
    }

    public function test_it_should_exec_a_job()
    {
        $this
            ->given(
                $givenOptions = array('aa' => 'john', 'bb' => 'doe'),
                $expectedOptions = array(
                    'aa' => 'john',
                    'bb' => 'doe', 
                    'cc' => 'dodo'
                ),
                $factory = $this->getMockJobFactory(),
                $resolvedJob = new \mock\Rezzza\Jobflow\ResolvedJob($this->jobType, array(), $this->resolvedParent),
                $ed = new \mock\Symfony\Component\EventDispatcher\EventDispatcherInterface,
                $config = new \mock\Rezzza\Jobflow\JobConfig('name', $ed)
            )
            ->then(
                $options = $resolvedJob->execJob($config, $givenOptions)
            )
            ->mock($this->jobType)
                ->call('buildExec')
                    ->withArguments($config, $expectedOptions)
                    ->once()

            ->mock($this->parentType)
                ->call('buildExec')
                    ->withArguments($config, $expectedOptions)
                    ->once()
        ;
    }

    private function getMockJobType($initOptions, $execOptions)
    {
        $mock = new \mock\Rezzza\Jobflow\JobTypeInterface;
        $mock->getMockController()->setInitOptions = function($resolver) use ($initOptions) {
            $resolver->setDefaults($initOptions);
        };
        $mock->getMockController()->setExecOptions = function($resolver) use ($execOptions) {
            $resolver->setDefaults($execOptions);
        };

        return $mock;
    }

    private function getMockJobFactory()
    {
        $this->mockGenerator->orphanize('__construct');
        $registry = new \mock\Rezzza\Jobflow\JobRegistry;

        return new \mock\Rezzza\Jobflow\JobFactory($registry);
    }
}