<?php

declare(strict_types=1);

namespace Oscmarb\MigrationsMultipleDatabase\Bundle\DependencyInjection;

use Doctrine\Bundle\MigrationsBundle\DependencyInjection\Configuration as DoctrineMigrationsConfiguration;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration extends DoctrineMigrationsConfiguration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('doctrine_migrations_multiple_database');
        $entityManagersNodeDefinition = $treeBuilder
            ->getRootNode()
            ->children()
            ->arrayNode('entity_managers')
            ->arrayPrototype();

        $doctrineRootNode = parent::getConfigTreeBuilder()->getRootNode();

        if (false === $doctrineRootNode instanceof ArrayNodeDefinition) {
            throw new \RuntimeException('Invalid NodeDefinition type');
        }

        foreach ($doctrineRootNode->getChildNodeDefinitions() as $childNodeDefinition) {
            $reflectionNodeDefinition = new \ReflectionClass($childNodeDefinition);
            $nameProperty = $reflectionNodeDefinition->getProperty('name');
            $name = $nameProperty->getValue($childNodeDefinition);

            if ('em' === $name) {
                continue;
            }

            $entityManagersNodeDefinition->append($childNodeDefinition);
        }

        return $treeBuilder;
    }
}
