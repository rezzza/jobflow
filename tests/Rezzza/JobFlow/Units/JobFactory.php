<?php

namespace Rezzza\JobFlow\Tests\Units;

use mageekguy\atoum as Units;

use Rezzza\JobFlow\JobFactory as TestedClass;

class JobFactory extends Units\Test
{
    protected $registry;

    protected $factory;

    public function beforeTestMethod($method)
    {
        $this->mockGenerator->orphanize('__construct');
        $this->registry = new \mock\Rezzza\JobFlow\JobRegistry;
        $this->factory = new TestedClass($this->registry);
    }

    public function test_it_creates_simple_builder()
    {
        $options = array('a' => '1', 'b' => '2');
        $resolved = $this->getMockResolvedJob();
        
        $resolved->getMockController()->createBuilder = 'jean-marc';
        $this->registry->getMockController()->getType = $resolved; 

        $this
            ->if($builder = $this->factory->createNamedBuilder('name', 'type', null, $options))
                ->mock($this->registry)
                    ->call('getType')
                        ->withArguments('type')
                        ->once()

                ->mock($resolved)
                    ->call('createBuilder')
                        ->withArguments('name', $this->factory, $options)
                        ->once()

                ->variable($builder)
                    ->isEqualTo('jean-marc')
        ;
    }

    public function test_it_creates_builder_with_type()
    {
        $options = array('io' => '1', 'b' => '2');
        $resolved = $this->getMockResolvedJob();
        $type = new \mock\Rezzza\JobFlow\Extension\Core\Type\JobType();
        $factory = $this->getMockFactory();
        $io = $this->getMockIo();

        $resolved->getMockController()->createBuilder = 'jean-marc';
        $factory->getMockController()->createResolvedType = $resolved; 

        $this
            ->if($builder = $factory->createNamedBuilder('name', $type, $io, $options))
                ->mock($factory)
                    ->call('createResolvedType')
                        ->withArguments($type)
                        ->once()

                ->mock($resolved)
                    ->call('createBuilder')
                        ->withArguments('name', $factory, $options)
                        ->once()

                ->variable($builder)
                    ->isEqualTo('jean-marc')
        ;
    }

    public function test_it_creates_builder_with_type_with_parent()
    {
        $options = array('io' => '1', 'b' => '2');
        $factory = $this->getMockFactory();
        $type = new \mock\Rezzza\JobFlow\Extension\Core\Type\JobType();
        $parentType = new \mock\Rezzza\JobFlow\Extension\Core\Type\JobType();
        $resolved = $this->getMockResolvedJob();
        $parentResolved = $this->getMockResolvedJob();

        $type->getMockController()->getParent = 'flex';
        $this->registry->getMockController()->getType = $parentType;
        $factory->getMockController()->createResolvedType = $resolved;
        $resolved->getMockController()->createBuilder = 'jean-marc'; 

        $this
            ->if($builder = $factory->createNamedBuilder('name', $type, null, $options))

                ->mock($this->registry)
                    ->call('getType')
                        ->withArguments('flex')
                        ->once()

                ->mock($factory)
                    ->call('createResolvedType')
                        ->withArguments($type, $parentResolved)
                        ->once()

                ->mock($resolved)
                    ->call('createBuilder')
                        ->withArguments('name', $factory, $options)
                        ->once()
        ;
    }

    public function test_it_creates_builder_with_type_with_parent_job()
    {
        $options = array('io' => '1', 'b' => '2');
        $factory = $this->getMockFactory();
        $type = new \mock\Rezzza\JobFlow\Extension\Core\Type\JobType();
        $parentType = new \mock\Rezzza\JobFlow\Extension\Core\Type\JobType();
        $resolved = $this->getMockResolvedJob();
        $parentResolved = $this->getMockResolvedJob();
        $io = $this->getMockIo();

        $resolved->getMockController()->createBuilder = 'jean-marc';
        $type->getMockController()->getParent = $parentType;
        $factory->getMockController()->createResolvedType = $resolved;

        $this
            ->if($builder = $factory->createNamedBuilder('name', $type, $io, $options))

                ->mock($factory)
                    ->call('createResolvedType')
                        ->withArguments($parentType, $io, null)
                        ->withArguments($type, $io, $parentType)

                ->mock($resolved)
                    ->call('createBuilder')
                        ->withArguments('name', $factory, $options)
                        ->once()

                ->variable($builder)
                    ->isEqualTo('jean-marc')
        ;
    }

    public function test_it_creates_builder_with_resolved_type()
    {
        $options = array('a' => '1', 'b' => '2');
        $resolved = $this->getMockResolvedJob();

        $resolved->getMockController()->createBuilder = 'jean-marc';

        $this
            ->if($builder = $this->factory->createNamedBuilder('name', $resolved, null, $options))
                ->mock($resolved)
                    ->call('createBuilder')
                        ->withArguments('name', $this->factory, $options)
                        ->once()

                ->variable($builder)
                    ->isEqualTo('jean-marc')
        ;
    }

    public function test_it_creates_builder_and_fills_io()
    {
        $givenOptions = array('a' => '1', 'b' => '2');
        $io = $this->getMockIo();
        $expectedOptions = array_merge($givenOptions, array('io' => $io));
        $resolved = $this->getMockResolvedJob();

        $resolved->getMockController()->createBuilder = 'jean-marc';
        $this->registry->getMockController()->getType = $resolved; 

        $this
            ->if($builder = $this->factory->createNamedBuilder('name', 'type', $io, $givenOptions))
                ->mock($resolved)
                    ->call('createBuilder')
                        ->withArguments('name', $this->factory, $expectedOptions)
                        ->once()

                ->mock($this->registry)
                    ->call('getType')
                        ->withArguments('type')
                        ->once()

                ->variable($builder)
                    ->isEqualTo('jean-marc')
        ;
    }

    public function test_it_creates_builder_and_keeps_io()
    {
        $io = $this->getMockIo();
        $options = array('a' => '1', 'b' => '2', 'io' => $io);
        $resolved = $this->getMockResolvedJob();

        $resolved->getMockController()->createBuilder = 'jean-marc';
        $this->registry->getMockController()->getType = $resolved; 

        $this
            ->if($builder = $this->factory->createNamedBuilder('name', 'type', null, $options))
                ->mock($resolved)
                    ->call('createBuilder')
                        ->withArguments('name', $this->factory, $options)
                        ->once()

                ->mock($this->registry)
                    ->call('getType')
                        ->withArguments('type')
                        ->once()

                ->variable($builder)
                    ->isEqualTo('jean-marc')
        ;
    }

    public function test_create_builder_accepts_only_specified_class()
    {
        $factory = $this->factory;

        $this
            ->exception(function() use ($factory) {
                $factory->createNamedBuilder('name', new \stdClass());
            })
                ->hasMessage('Type "stdClass" should be a string, JobTypeInterface or ResolvedJob')
        ;
    }

    public function test_it_creates_a_job_with_string_type()
    {
        $options = array('a' => '1', 'b' => '2');
        $resolved = $this->getMockResolvedJob();
        $builder = $this->getMockBuilder();

        $this->registry->getMockController()->getType = $resolved;
        $resolved->getMockController()->createBuilder = $builder;
        $builder->getMockController()->getJob = 'JOB';

        $this
            ->if($job = $this->factory->create('TYPE', $options))

                ->mock($this->registry)
                    ->call('getType')
                        ->withArguments('TYPE')
                        ->once()

                ->mock($resolved)
                    ->call('createBuilder')
                        ->withArguments('TYPE', $this->factory, $options)
                        ->once()

                ->mock($builder)
                    ->call('getJob')
                        ->once()

                ->variable($job)
                    ->isEqualTo('JOB')
        ;
    }

    public function test_it_creates_resolved_job()
    {
        $type = new \mock\Rezzza\JobFlow\Extension\Core\Type\JobType();
        $parent = $this->getMockResolvedJob();

        $this
            ->if($resolved = $this->factory->createResolvedType($type, $parent))

                ->object($resolved)
                    ->isInstanceOf('Rezzza\JobFlow\ResolvedJob')
        ;
    }

    private function getMockResolvedJob()
    {
        $this->mockGenerator->orphanize('__construct');

        return new \mock\Rezzza\JobFlow\ResolvedJob;
    }

    private function getMockFactory()
    {
        return new \mock\Rezzza\JobFlow\JobFactory($this->registry);
    }

    private function getMockIo()
    {
        return new \mock\Rezzza\JobFlow\Io\IoDescriptor(null);
    }

    private function getMockBuilder()
    {
        $this->mockGenerator->orphanize('__construct');

        return new \mock\Rezzza\JobFlow\JobBuilder;
    }
}