<?php

namespace Rezzza\Jobflow\Plugin\SymfonyBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class RezzzaJobflowExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);
        $definitions = array();

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config/services'));
        $loader->load('cli.xml');
        $loader->load('job.xml');
        $loader->load('job_types.xml');
        $loader->load('orm.xml');
        $loader->load('monolog.xml');

        // Checks if Thumper lib is loaded to use rabbitmq extension
        if ($this->isRabbitMqLoaded()) {
            $loader->load('rabbitmq.xml');

            if (isset($config['transports']['rabbitmq'])) {
                $rabbitmqConfig = $config['transports']['rabbitmq'];
                $connections = $this->loadRabbitmqConnections($rabbitmqConfig);
                $rabbitmq = $container->getDefinition('rezzza_jobflow.rabbitmq');
                $rabbitmq->replaceArgument(0, $connections);
                $definitions = array_merge($definitions, $connections);
                $definitions = array_merge($definitions, $this->loadRabbitmqDefinitions($rabbitmqConfig));
            }
        }

        foreach ($definitions as $id => $definition) {
            $container->setDefinition($id, $definition);
        }
    }

    public function isRabbitMqLoaded()
    {
        return class_exists('Thumper\RpcClient');
    }

    protected function loadRabbitmqConnections($config)
    {
        $definitions = array();

        foreach ($config['connections'] as $name => $connection) {
            $connections[$name] = new Definition(
                '%rezzza_jobflow.rabbitmq.connection.class%',
                array(
                    $connection['host'],
                    $connection['port'],
                    $connection['user'],
                    $connection['password'],
                    $connection['vhost']
                )
            );

            $id = sprintf('rezzza_jobflow.rabbitmq.%s_connection', $name);
            $definitions[$id] = $connections[$name];
        }

        return $definitions;
    }

    protected function loadRabbitmqDefinitions($config)
    {
        return array(
            'rezzza_jobflow.rabbitmq.rpc_client' => $this->createRpcClient(new Reference('rezzza_jobflow.rabbitmq.jobflow_connection')),
            'rezzza_jobflow.rabbitmq.rpc_server' => $this->createRpcServer(new Reference('rezzza_jobflow.rabbitmq.jobflow_connection')),
            'rezzza_jobflow.rabbitmq.producer' => $this->createProducer(new Reference('rezzza_jobflow.rabbitmq.jobflow_connection'))
        );
    }

    /**
     * @param Reference $connection
     */
    protected function createRpcClient($connection)
    {
        $rpcClient = new Definition('%rezzza_jobflow.rabbitmq.rpc_client.class%', array($connection));
        $rpcClient->addMethodCall('initClient');
        $rpcClient->addMethodCall('setJobflowFactory', array(new Reference('rezzza_jobflow.flow')));

        return $rpcClient;
    }

    /**
     * @param Reference $connection
     */
    protected function createRpcServer($connection)
    {
        $rpcServer = new Definition('%rezzza_jobflow.rabbitmq.rpc_server.class%', array($connection));
        $rpcServer->addMethodCall('initServer', array('jobflow'));
        $rpcServer->addMethodCall('setCallback', array(new Reference('rezzza_jobflow.rabbitmq.worker')));

        return $rpcServer;
    }

    /**
     * @param Reference $connection
     */
    protected function createProducer($connection)
    {
        return new Definition('%rezzza_jobflow.rabbitmq.producer.class%', array($connection));
    }
}
