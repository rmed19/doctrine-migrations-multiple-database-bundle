<?php

declare(strict_types=1);

namespace Oscmarb\MigrationsMultipleDatabase\Bundle\DependencyInjection;

use Doctrine\Bundle\MigrationsBundle\Collector\MigrationsCollector;
use Doctrine\Bundle\MigrationsBundle\Collector\MigrationsFlattener;
use Doctrine\Bundle\MigrationsBundle\DependencyInjection\DoctrineMigrationsExtension;
use Doctrine\Migrations\Configuration\EntityManager\ExistingEntityManager;
use Doctrine\Migrations\Configuration\Migration\ExistingConfiguration;
use Doctrine\Migrations\DependencyFactory;
use Doctrine\Migrations\Metadata\Storage\MetadataStorage;
use Doctrine\Migrations\Metadata\Storage\TableMetadataStorageConfiguration;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Argument\ServiceClosureArgument;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;

class DoctrineMigrationsMultipleDatabaseExtension extends DoctrineMigrationsExtension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();

        $config = $this->processConfiguration($configuration, $configs);

        $locator = new FileLocator(__DIR__.'/../Resources/config/');
        $loader = new YamlFileLoader($container, $locator);

        $loader->load('services.yaml');

        foreach ($config['entity_managers'] as $name => $connection) {
            $this->loadEntityManagerConfiguration($name, $connection, $container);
        }
    }

    /**
     * @param mixed[] $connection
     */
    private function loadEntityManagerConfiguration(string $name, array $connection, ContainerBuilder $container): void
    {
        $configuration = $container->setDefinition(sprintf('doctrine.migrations_multiple_database.%s_entity_manager.configuration', $name), new ChildDefinition('doctrine.migrations_multiple_database.connection_configuration'));
        $container
            ->register(sprintf('doctrine.migrations_multiple_database.%s_entity_manager.configuration_loader', $name), ExistingConfiguration::class)
            ->addArgument(new Reference(sprintf('doctrine.migrations_multiple_database.%s_entity_manager.configuration', $name)));

        $container
            ->register(sprintf('doctrine.migrations_multiple_database.%s_em_loader', $name), ExistingEntityManager::class)
            ->addArgument(new Reference(sprintf('doctrine.orm.%s_entity_manager', $name)));

        $diDefinition = $container->setDefinition(sprintf('doctrine.migrations_multiple_database.%s_entity_manager.dependency_factory', $name), new ChildDefinition('doctrine.migrations_multiple_database.dependency_factory'));
        $diDefinition
            ->setFactory([DependencyFactory::class, 'fromEntityManager'])
            ->setArgument(0, new Reference(sprintf('doctrine.migrations_multiple_database.%s_entity_manager.configuration_loader', $name)))
            ->setArgument(1, new Reference(sprintf('doctrine.migrations_multiple_database.%s_em_loader', $name)));

        /** @var array<string,string> $migrationPaths */
        $migrationPaths = $connection['migrations_paths'];

        foreach ($migrationPaths as $migrationNamespace => $migrationPath) {
            $migrationDirectory = $this->checkIfBundleRelativePath($migrationPath, $container);
            $configuration->addMethodCall('addMigrationsDirectory', [$migrationNamespace, $migrationDirectory]);
        }

        /** @var string[] $migrationClasses */
        $migrationClasses = $connection['migrations'];

        foreach ($migrationClasses as $migrationClass) {
            $configuration->addMethodCall('addMigrationClass', [$migrationClass]);
        }

        if (false !== $connection['organize_migrations']) {
            $configuration->addMethodCall('setMigrationOrganization', [$connection['organize_migrations']]);
        }

        if (null !== $connection['custom_template']) {
            $configuration->addMethodCall('setCustomTemplate', [$connection['custom_template']]);
        }

        $configuration->addMethodCall('setAllOrNothing', [$connection['all_or_nothing']]);
        $configuration->addMethodCall('setCheckDatabasePlatform', [$connection['check_database_platform']]);

        if ($connection['enable_profiler']) {
            $this->registerCollector($container);
        }

        $container
            ->getDefinition('doctrine.migrations_multiple_database.loader')
            ->addMethodCall('addDependencyFactory', [$name, new Reference(sprintf('doctrine.migrations_multiple_database.%s_entity_manager.dependency_factory', $name))]);

        foreach ($connection['services'] as $doctrineId => $symfonyId) {
            $diDefinition->addMethodCall('setDefinition', [$doctrineId, new ServiceClosureArgument(new Reference($symfonyId))]);
        }

        foreach ($connection['factories'] as $doctrineId => $symfonyId) {
            $diDefinition->addMethodCall('setDefinition', [$doctrineId, new Reference($symfonyId)]);
        }

        if (!isset($connection['services'][MetadataStorage::class])) {
            $storageConfiguration = $connection['storage']['table_storage'];

            $storageDefinition = new Definition(TableMetadataStorageConfiguration::class);
            $container->setDefinition(sprintf('doctrine.migrations_multiple_database.storage.%s_table_storage', $name), $storageDefinition);
            $container->setAlias('doctrine.migrations_multiple_database.storage.%s_metadata_storage', 'doctrine.migrations_multiple_database.storage.%s_table_storage');

            if (null !== $storageConfiguration['table_name']) {
                $storageDefinition->addMethodCall('setTableName', [$storageConfiguration['table_name']]);
            }
            if (null !== $storageConfiguration['version_column_name']) {
                $storageDefinition->addMethodCall('setVersionColumnName', [$storageConfiguration['version_column_name']]);
            }
            if (null !== $storageConfiguration['version_column_length']) {
                $storageDefinition->addMethodCall('setVersionColumnLength', [$storageConfiguration['version_column_length']]);
            }
            if (null !== $storageConfiguration['executed_at_column_name']) {
                $storageDefinition->addMethodCall('setExecutedAtColumnName', [$storageConfiguration['executed_at_column_name']]);
            }
            if (null !== $storageConfiguration['execution_time_column_name']) {
                $storageDefinition->addMethodCall('setExecutionTimeColumnName', [$storageConfiguration['execution_time_column_name']]);
            }

            $configuration->addMethodCall('setMetadataStorageConfiguration', [new Reference(sprintf('doctrine.migrations_multiple_database.storage.%s_table_storage', $name))]);
        }
    }

    private function checkIfBundleRelativePath(string $path, ContainerBuilder $container): string
    {
        if (isset($path[0]) && '@' === $path[0]) {
            $pathParts = explode('/', $path);
            $bundleName = substr($pathParts[0], 1);

            $bundlePath = $this->getBundlePath($bundleName, $container);

            return $bundlePath.substr($path, strlen('@'.$bundleName));
        }

        return $path;
    }

    private function getBundlePath(string $bundleName, ContainerBuilder $container): string
    {
        $bundleMetadata = $container->getParameter('kernel.bundles_metadata');

        if (false === is_array($bundleMetadata) || false === isset($bundleMetadata[$bundleName])) {
            /* @phpstan-ignore-next-line */
            throw new \RuntimeException(sprintf('The bundle "%s" has not been registered, available bundles: %s', $bundleName, implode(', ', array_keys($bundleMetadata))));
        }

        return $bundleMetadata[$bundleName]['path'];
    }

    private function registerCollector(ContainerBuilder $container): void
    {
        $flattenerDefinition = new Definition(MigrationsFlattener::class);
        $container->setDefinition('doctrine_migrations.migrations_flattener', $flattenerDefinition);

        $collectorDefinition = new Definition(MigrationsCollector::class, [
            new Reference('doctrine.migrations.dependency_factory'),
            new Reference('doctrine_migrations.migrations_flattener'),
        ]);
        $collectorDefinition
            ->addTag('data_collector', [
                'template' => '@DoctrineMigrations/Collector/migrations.html.twig',
                'id' => 'doctrine_migrations',
                'priority' => '249',
            ]);
        $container->setDefinition('doctrine_migrations.migrations_collector', $collectorDefinition);
    }
}
